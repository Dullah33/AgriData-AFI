<?php

namespace App\Http\Controllers\Petani;

use App\Exceptions\TransisiStatusTidakValidException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\AlasanRequest;
use App\Models\Transaksi;
use App\Services\Marketplace\PembayaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Pesanan masuk untuk produk milik petani yang sedang login.
 *
 * Alur dari sisi petani:
 * - Menunggu : pembeli belum membayar / pembayaran belum diverifikasi.
 *              Petani boleh menolak (stok dikembalikan) selama pembeli
 *              belum mengirim pembayaran.
 * - Diproses : pembayaran lunas, petani menyiapkan dan mengirim barang,
 *              lalu menandai pesanan selesai.
 * Transaksi lama tanpa tagihan pembayaran masih bisa dikonfirmasi manual.
 */
class PesananController extends Controller
{
    public function __construct(private PembayaranService $layanan)
    {
    }

    // GET /petani/pesanan?status=diproses
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), array_keys(Transaksi::LABEL_STATUS), true)
            ? $request->query('status')
            : null;

        $dasar = Transaksi::untukPetani(Auth::id());

        $jumlahPerStatus = (clone $dasar)
            ->selectRaw('status_transaksi, COUNT(*) as total')
            ->groupBy('status_transaksi')
            ->pluck('total', 'status_transaksi');

        $pesanans = (clone $dasar)
            ->with(['produk', 'pembeli', 'pembayaran'])
            ->when($status, fn ($q) => $q->status($status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('petani.pesanan.index', compact('pesanans', 'status', 'jumlahPerStatus'));
    }

    // POST /petani/pesanan/{id}/konfirmasi  (hanya transaksi lama tanpa pembayaran online)
    public function konfirmasi(Transaksi $transaksi)
    {
        $this->otorisasiPetani($transaksi);

        if ($transaksi->pembayaran_id) {
            return back()->with('error', 'Pesanan ini otomatis diproses setelah pembayaran pembeli lunas.');
        }

        return $this->jalankan(fn () => $transaksi->tandaiDiproses(), 'Pesanan dikonfirmasi dan siap dikirim.');
    }

    // POST /petani/pesanan/{id}/selesai
    public function selesai(Transaksi $transaksi)
    {
        $this->otorisasiPetani($transaksi);

        return $this->jalankan(fn () => $transaksi->tandaiSelesai(), 'Pesanan ditandai selesai. Pembeli sekarang bisa memberi ulasan.');
    }

    // POST /petani/pesanan/{id}/tolak
    public function tolak(AlasanRequest $request, Transaksi $transaksi)
    {
        $this->otorisasiPetani($transaksi);

        return $this->jalankan(
            fn () => $this->layanan->tolakTransaksiOlehPetani($transaksi, $request->validated('alasan')),
            'Pesanan ditolak dan stok dikembalikan.'
        );
    }

    // Pastikan petani hanya bisa mengelola pesanan atas produknya sendiri
    private function otorisasiPetani(Transaksi $transaksi): void
    {
        abort_unless($transaksi->produk?->petani_id === Auth::id(), 403);
    }

    private function jalankan(\Closure $aksi, string $pesanSukses)
    {
        try {
            $aksi();
        } catch (TransisiStatusTidakValidException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $pesanSukses);
    }
}
