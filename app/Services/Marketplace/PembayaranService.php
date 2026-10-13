<?php

namespace App\Services\Marketplace;

use App\Exceptions\TransisiStatusTidakValidException;
use App\Models\Pembayaran;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Semua perubahan status pembayaran lewat kelas ini. Setiap method
 * mengunci baris pembayaran (SELECT ... FOR UPDATE) di dalam transaksi
 * database, supaya aksi yang datang bersamaan (mis. webhook Midtrans
 * terkirim dua kali, atau admin dan scheduler bergerak di detik yang
 * sama) tidak menghasilkan stok ganda atau status yang bertabrakan.
 */
class PembayaranService
{
    // Bukti transfer berisi data rekening pembeli, jadi disimpan di disk
    // privat dan hanya bisa dibuka pemiliknya atau Admin lewat controller.
    public const DISK_BUKTI = 'local';

    public function __construct(private MidtransGateway $midtrans)
    {
    }

    public function simpanBuktiTransfer(Pembayaran $pembayaran, UploadedFile $file): void
    {
        $path = $file->store('bukti-transfer', self::DISK_BUKTI);

        if (! $path) {
            throw new \RuntimeException('Bukti transfer gagal disimpan. Periksa permission folder storage.');
        }

        try {
            DB::transaction(function () use ($pembayaran, $path) {
                $segar = $this->kunci($pembayaran);

                if (! $segar->bisaUnggahBukti()) {
                    throw new TransisiStatusTidakValidException($segar->lewatBatasBayar()
                        ? 'Batas waktu pembayaran sudah lewat.'
                        : 'Pembayaran ini sudah tidak menunggu bukti transfer.');
                }

                $lama = $segar->bukti_transfer;

                $segar->update([
                    'bukti_transfer' => $path,
                    'bukti_diunggah_at' => now(),
                    'status' => Pembayaran::MENUNGGU_VERIFIKASI,
                    'catatan_verifikasi' => null,
                ]);

                if ($lama) {
                    DB::afterCommit(fn () => Storage::disk(self::DISK_BUKTI)->delete($lama));
                }
            });
        } catch (\Throwable $e) {
            Storage::disk(self::DISK_BUKTI)->delete($path);

            throw $e;
        }

        $pembayaran->refresh();
    }

    /**
     * Tandai lunas dan teruskan semua transaksinya ke status Diproses.
     * Idempoten: memanggil ulang untuk pembayaran yang sudah lunas tidak
     * melakukan apa-apa (notifikasi Midtrans bisa dikirim berulang).
     */
    public function konfirmasiLunas(Pembayaran $pembayaran, ?User $verifikator = null, array $dataGateway = []): void
    {
        DB::transaction(function () use ($pembayaran, $verifikator, $dataGateway) {
            $segar = $this->kunci($pembayaran);

            if ($segar->status === Pembayaran::LUNAS) {
                return;
            }

            if ($segar->sudahFinal()) {
                throw new TransisiStatusTidakValidException("Pembayaran sudah ditutup dengan status \"{$segar->labelStatus()}\".");
            }

            if ($verifikator && $segar->status !== Pembayaran::MENUNGGU_VERIFIKASI) {
                throw new TransisiStatusTidakValidException('Pembayaran ini belum punya bukti transfer untuk diverifikasi.');
            }

            $segar->update([
                'status' => Pembayaran::LUNAS,
                'dibayar_at' => now(),
                'diverifikasi_oleh' => $verifikator?->id,
                'diverifikasi_at' => $verifikator ? now() : null,
            ] + $dataGateway);

            $segar->transaksis()
                ->where('status_transaksi', Transaksi::MENUNGGU)
                ->get()
                ->each->tandaiDiproses();
        });

        $pembayaran->refresh();
    }

    /**
     * Admin menolak bukti transfer (buram, nominal salah, dll). Pembeli
     * mendapat batas waktu baru untuk mengunggah ulang.
     */
    public function tolakBukti(Pembayaran $pembayaran, User $admin, string $alasan): void
    {
        DB::transaction(function () use ($pembayaran, $admin, $alasan) {
            $segar = $this->kunci($pembayaran);

            if ($segar->status !== Pembayaran::MENUNGGU_VERIFIKASI) {
                throw new TransisiStatusTidakValidException('Pembayaran ini tidak sedang menunggu verifikasi.');
            }

            $segar->update([
                'status' => Pembayaran::MENUNGGU_PEMBAYARAN,
                'catatan_verifikasi' => $alasan,
                'diverifikasi_oleh' => $admin->id,
                'diverifikasi_at' => now(),
                'batas_bayar' => now()->addHours(config('marketplace.batas_bayar_jam', 24)),
            ]);
        });

        $pembayaran->refresh();
    }

    /**
     * Tutup pembayaran yang belum lunas (dibatalkan pembeli, kedaluwarsa,
     * atau gagal di gateway) dan batalkan semua transaksinya sehingga stok
     * kembali. Idempoten untuk pembayaran yang sudah ditutup.
     */
    public function tutup(Pembayaran $pembayaran, string $status, string $alasan, array $dataGateway = []): void
    {
        DB::transaction(function () use ($pembayaran, $status, $alasan, $dataGateway) {
            $segar = $this->kunci($pembayaran);

            if ($segar->status === Pembayaran::LUNAS) {
                throw new TransisiStatusTidakValidException('Pembayaran yang sudah lunas tidak bisa dibatalkan.');
            }

            if ($segar->sudahFinal()) {
                return;
            }

            $segar->transaksis()
                ->where('status_transaksi', Transaksi::MENUNGGU)
                ->get()
                ->each(fn (Transaksi $t) => $t->batalkan($alasan));

            $segar->update(['status' => $status] + $dataGateway);
        });

        $pembayaran->refresh();
    }

    /**
     * Petani menolak satu produk dalam pesanan (mis. stok di lapangan
     * ternyata rusak). Hanya boleh selama pembeli belum mengunggah bukti
     * atau membayar, karena setelah itu perlu pengembalian dana lewat Admin.
     * Total tagihan dihitung ulang dari produk yang tersisa.
     */
    public function tolakTransaksiOlehPetani(Transaksi $transaksi, string $alasan): void
    {
        DB::transaction(function () use ($transaksi, $alasan) {
            $pembayaran = $transaksi->pembayaran_id ? Pembayaran::whereKey($transaksi->pembayaran_id)->lockForUpdate()->first() : null;

            if ($pembayaran && $pembayaran->status !== Pembayaran::MENUNGGU_PEMBAYARAN) {
                throw new TransisiStatusTidakValidException(
                    'Pembeli sudah mengirim pembayaran untuk pesanan ini. Hubungi Admin untuk pembatalan dan pengembalian dana.'
                );
            }

            $transaksi->batalkan('Ditolak petani: '.$alasan);

            if (! $pembayaran) {
                return;
            }

            $sisa = (float) $pembayaran->transaksis()->where('status_transaksi', '!=', Transaksi::BATAL)->sum('total_harga');

            $pembayaran->update($sisa > 0
                // Nominal berubah: token Midtrans lama tidak berlaku lagi
                ? ['jumlah' => $sisa, 'snap_token' => null, 'gateway_order_id' => null]
                : ['status' => Pembayaran::DIBATALKAN]);
        });
    }

    /**
     * Kedaluwarsakan pembayaran yang lewat batas bayar. Dipanggil scheduler
     * (pembayaran:kedaluwarsa) dan juga saat pembeli membuka halaman
     * pesanannya, supaya tetap jalan walau scheduler belum diaktifkan.
     */
    public function kedaluwarsakanJatuhTempo(?int $userId = null): int
    {
        $jumlah = 0;

        Pembayaran::jatuhTempo()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->get()
            ->each(function (Pembayaran $pembayaran) use (&$jumlah) {
                try {
                    $this->tutup($pembayaran, Pembayaran::KEDALUWARSA, 'Melewati batas waktu pembayaran.');
                    $jumlah++;
                } catch (\Throwable $e) {
                    report($e);
                }
            });

        return $jumlah;
    }

    /**
     * Siapkan Snap token untuk halaman pembayaran Midtrans. Di mode
     * simulasi cukup memberi order_id supaya tombol simulasi bisa dipakai.
     */
    public function siapkanMidtrans(Pembayaran $pembayaran): ?string
    {
        if ($pembayaran->metode !== Pembayaran::METODE_MIDTRANS || ! $pembayaran->bisaDibayar()) {
            return null;
        }

        if ($this->midtrans->modeSimulasi()) {
            if (! $pembayaran->gateway_order_id) {
                $pembayaran->update(['gateway_order_id' => $pembayaran->kode.'-SIM']);
            }

            return null;
        }

        if (! $this->midtrans->punyaKunci()) {
            return null;
        }

        if (! $pembayaran->snap_token) {
            $hasil = $this->midtrans->buatTransaksi($pembayaran);
            $pembayaran->update(['snap_token' => $hasil['token'], 'gateway_order_id' => $hasil['order_id']]);
        }

        return $pembayaran->snap_token;
    }

    /**
     * Proses notifikasi status dari Midtrans (webhook asli maupun simulasi).
     */
    public function prosesNotifikasiMidtrans(array $payload): Pembayaran
    {
        $pembayaran = Pembayaran::where('gateway_order_id', $payload['order_id'] ?? null)->firstOrFail();
        $status = $this->midtrans->petakanStatus($payload);

        $dataGateway = [
            'gateway_transaction_id' => $payload['transaction_id'] ?? $pembayaran->gateway_transaction_id,
            'gateway_payload' => $payload,
        ];

        match ($status) {
            Pembayaran::LUNAS => $this->konfirmasiLunas($pembayaran, null, $dataGateway),
            Pembayaran::GAGAL, Pembayaran::KEDALUWARSA => $this->tutup(
                $pembayaran, $status, 'Pembayaran Midtrans '.($payload['transaction_status'] ?? 'gagal').'.', $dataGateway
            ),
            default => $pembayaran->update($dataGateway),
        };

        Log::info('Midtrans: notifikasi diproses.', [
            'kode' => $pembayaran->kode,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'status_baru' => $pembayaran->fresh()->status,
        ]);

        return $pembayaran->refresh();
    }

    private function kunci(Pembayaran $pembayaran): Pembayaran
    {
        return Pembayaran::whereKey($pembayaran->getKey())->lockForUpdate()->firstOrFail();
    }
}
