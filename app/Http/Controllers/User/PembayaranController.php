<?php

namespace App\Http\Controllers\User;

use App\Exceptions\TransisiStatusTidakValidException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\UnggahBuktiTransferRequest;
use App\Models\Pembayaran;
use App\Services\Marketplace\MidtransGateway;
use App\Services\Marketplace\PembayaranService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    public function __construct(private PembayaranService $layanan, private MidtransGateway $midtrans)
    {
    }

    // GET /pembayaran/{kode}
    public function show(Pembayaran $pembayaran)
    {
        $this->pastikanPemilik($pembayaran);
        $this->layanan->kedaluwarsakanJatuhTempo(Auth::id());
        $pembayaran->refresh()->load('transaksis.produk.petani');

        $snapToken = null;
        $galatGateway = null;

        try {
            $snapToken = $this->layanan->siapkanMidtrans($pembayaran);
        } catch (\Throwable $e) {
            Log::error('Midtrans: gagal membuat transaksi Snap.', ['kode' => $pembayaran->kode, 'error' => $e->getMessage()]);
            $galatGateway = 'Layanan Midtrans sedang tidak bisa dihubungi. Muat ulang halaman ini beberapa saat lagi.';
        }

        return view('user.pembayaran.show', [
            'pembayaran' => $pembayaran,
            'rekening' => config('marketplace.rekening'),
            'snapToken' => $snapToken,
            'galatGateway' => $galatGateway,
            'midtrans' => $this->midtrans,
        ]);
    }

    // POST /pembayaran/{kode}/bukti
    public function unggahBukti(UnggahBuktiTransferRequest $request, Pembayaran $pembayaran)
    {
        try {
            $this->layanan->simpanBuktiTransfer($pembayaran, $request->file('bukti_transfer'));
        } catch (TransisiStatusTidakValidException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Bukti transfer terkirim. Admin akan memverifikasi paling lambat 1x24 jam.');
    }

    // GET /pembayaran/{kode}/bukti
    public function bukti(Pembayaran $pembayaran)
    {
        $this->pastikanPemilik($pembayaran);
        abort_unless($pembayaran->bukti_transfer && Storage::disk(PembayaranService::DISK_BUKTI)->exists($pembayaran->bukti_transfer), 404);

        return Storage::disk(PembayaranService::DISK_BUKTI)->response($pembayaran->bukti_transfer);
    }

    // POST /pembayaran/{kode}/batal
    public function batal(Pembayaran $pembayaran)
    {
        $this->pastikanPemilik($pembayaran);

        if (! $pembayaran->bisaDibatalkanPembeli()) {
            return back()->with('error', 'Pesanan ini sudah tidak bisa dibatalkan.');
        }

        try {
            $this->layanan->tutup($pembayaran, Pembayaran::DIBATALKAN, 'Dibatalkan pembeli.');
        } catch (TransisiStatusTidakValidException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('user.pesanan')->with('success', 'Pesanan dibatalkan dan stok dikembalikan ke petani.');
    }

    // POST /pembayaran/{kode}/simulasi  (hanya mode simulasi Midtrans)
    public function simulasi(Pembayaran $pembayaran)
    {
        $this->pastikanPemilik($pembayaran);
        abort_unless($this->midtrans->modeSimulasi() && $pembayaran->metode === Pembayaran::METODE_MIDTRANS, 404);

        if (! $pembayaran->bisaDibayar() || ! $pembayaran->gateway_order_id) {
            return back()->with('error', 'Pembayaran ini sudah tidak bisa dibayar.');
        }

        $berhasil = request('hasil') !== 'gagal';

        try {
            $this->layanan->prosesNotifikasiMidtrans($this->midtrans->payloadSimulasi($pembayaran, $berhasil));
        } catch (TransisiStatusTidakValidException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with($berhasil ? 'success' : 'error', $berhasil
            ? 'Simulasi berhasil: pembayaran lunas dan pesanan diteruskan ke petani.'
            : 'Simulasi: pembayaran kedaluwarsa, pesanan dibatalkan dan stok dikembalikan.');
    }

    private function pastikanPemilik(Pembayaran $pembayaran): void
    {
        abort_unless($pembayaran->user_id === Auth::id(), 404);
    }
}
