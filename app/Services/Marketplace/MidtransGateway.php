<?php

namespace App\Services\Marketplace;

use App\Models\Pembayaran;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Integrasi Midtrans Snap (https://docs.midtrans.com/reference/snap-api).
 *
 * Dua mode:
 * - Asli   : MIDTRANS_SERVER_KEY diisi. Halaman pembayaran membuka popup
 *            Snap, lalu Midtrans mengirim notifikasi (webhook) ke
 *            /webhook/midtrans saat status pembayaran berubah.
 * - Simulasi: tanpa server key, MIDTRANS_SIMULASI=true, dan bukan
 *            production. Halaman pembayaran menampilkan tombol untuk
 *            mensimulasikan hasil pembayaran. Tombol itu menjalankan alur
 *            yang sama persis dengan notifikasi asli.
 */
class MidtransGateway
{
    public function punyaKunci(): bool
    {
        return filled(config('services.midtrans.server_key'));
    }

    public function modeSimulasi(): bool
    {
        return ! $this->punyaKunci()
            && config('services.midtrans.simulasi')
            && ! app()->isProduction();
    }

    public function tersedia(): bool
    {
        return $this->punyaKunci() || $this->modeSimulasi();
    }

    public function clientKey(): ?string
    {
        return config('services.midtrans.client_key');
    }

    public function snapJsUrl(): string
    {
        return $this->produksi()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /**
     * Membuat transaksi Snap dan mengembalikan token untuk popup pembayaran.
     *
     * @return array{token: string, redirect_url: ?string, order_id: string}
     *
     * @throws \RuntimeException kalau Midtrans menolak permintaan
     */
    public function buatTransaksi(Pembayaran $pembayaran): array
    {
        // order_id di Midtrans harus unik per percobaan. Kalau total berubah
        // (mis. ada item ditolak petani), token lama dibuang dan dibuat baru.
        $orderId = $pembayaran->kode.'-'.Str::upper(Str::random(4));

        $items = $pembayaran->transaksis()
            ->with('produk')
            ->where('status_transaksi', '!=', 'batal')
            ->get()
            ->map(fn ($t) => [
                'id' => 'PRD-'.$t->produk_id,
                'price' => (int) round((float) $t->harga_satuan),
                'quantity' => $t->jumlah,
                'name' => Str::limit($t->produk->nama_produk ?? 'Produk', 47, '...'),
            ])
            ->values();

        // Midtrans mewajibkan gross_amount sama persis dengan jumlah item
        $gross = $items->sum(fn ($i) => $i['price'] * $i['quantity']);

        // Batas waktu di Midtrans disamakan dengan batas bayar AgriScan supaya
        // tidak ada pembayaran yang masuk setelah pesanan dibatalkan otomatis.
        $menit = max(1, (int) now()->diffInMinutes($pembayaran->batas_bayar, false));

        $response = Http::withBasicAuth((string) config('services.midtrans.server_key'), '')
            ->acceptJson()
            ->timeout(20)
            ->post($this->snapUrl(), [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $gross,
                ],
                'item_details' => $items->all(),
                'customer_details' => [
                    'first_name' => $pembayaran->pembeli->username,
                    'email' => $pembayaran->pembeli->email,
                ],
                'expiry' => [
                    'start_time' => now()->format('Y-m-d H:i:s O'),
                    'unit' => 'minute',
                    'duration' => $menit,
                ],
                'callbacks' => [
                    'finish' => route('user.pembayaran.show', $pembayaran),
                ],
            ]);

        if (! $response->successful() || ! $response->json('token')) {
            throw new \RuntimeException('Midtrans menolak pembuatan transaksi: '.$response->status().' '.mb_substr($response->body(), 0, 300));
        }

        return [
            'token' => $response->json('token'),
            'redirect_url' => $response->json('redirect_url'),
            'order_id' => $orderId,
        ];
    }

    /**
     * Verifikasi signature_key notifikasi: SHA512(order_id + status_code + gross_amount + server_key).
     */
    public function signatureValid(array $payload): bool
    {
        if (! $this->punyaKunci()) {
            return false;
        }

        $harapan = hash('sha512',
            ($payload['order_id'] ?? '').
            ($payload['status_code'] ?? '').
            ($payload['gross_amount'] ?? '').
            config('services.midtrans.server_key')
        );

        return hash_equals($harapan, (string) ($payload['signature_key'] ?? ''));
    }

    /**
     * Petakan status Midtrans ke status Pembayaran. null = belum final
     * (pending, challenge, authorize), cukup dicatat.
     */
    public function petakanStatus(array $payload): ?string
    {
        $fraud = $payload['fraud_status'] ?? null;

        return match ($payload['transaction_status'] ?? null) {
            'settlement' => Pembayaran::LUNAS,
            'capture' => in_array($fraud, [null, 'accept'], true) ? Pembayaran::LUNAS : null,
            'deny', 'cancel', 'failure' => Pembayaran::GAGAL,
            'expire' => Pembayaran::KEDALUWARSA,
            default => null,
        };
    }

    // Payload berbentuk sama dengan notifikasi asli, untuk mode simulasi
    public function payloadSimulasi(Pembayaran $pembayaran, bool $berhasil): array
    {
        return [
            'order_id' => $pembayaran->gateway_order_id,
            'transaction_id' => 'SIM-'.Str::uuid(),
            'transaction_status' => $berhasil ? 'settlement' : 'expire',
            'status_code' => $berhasil ? '200' : '407',
            'gross_amount' => number_format((float) $pembayaran->jumlah, 2, '.', ''),
            'payment_type' => 'simulasi',
        ];
    }

    private function produksi(): bool
    {
        return (bool) config('services.midtrans.is_production');
    }

    private function snapUrl(): string
    {
        return $this->produksi()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }
}
