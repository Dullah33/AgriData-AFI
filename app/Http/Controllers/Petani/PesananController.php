<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\ProdukPanen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    // GET /petani/pesanan
    public function index()
    {
        // Ambil semua produk milik petani ini
        $produkIds = ProdukPanen::where('petani_id', Auth::id())
            ->pluck('id');

        $pesanans = Transaksi::with(['produk', 'pembeli'])
            ->whereIn('produk_id', $produkIds)
            ->latest()
            ->paginate(10);

        return view('petani.pesanan.index', compact('pesanans'));
    }

    // POST /petani/pesanan/{id}/konfirmasi
    public function konfirmasi(Transaksi $transaksi)
    {
        $this->otorisasiPetani($transaksi);

        abort_if($transaksi->status_transaksi !== 'pending', 403, 'Pesanan tidak bisa dikonfirmasi.');

        $transaksi->update(['status_transaksi' => 'diproses']);

        return back()->with('success', 'Pesanan dikonfirmasi, status diubah ke Diproses.');
    }

    // POST /petani/pesanan/{id}/selesai
    public function selesai(Transaksi $transaksi)
    {
        $this->otorisasiPetani($transaksi);

        abort_if($transaksi->status_transaksi !== 'diproses', 403);

        $transaksi->update(['status_transaksi' => 'selesai']);

        return back()->with('success', 'Pesanan ditandai selesai.');
    }

    // POST /petani/pesanan/{id}/tolak
    public function tolak(Transaksi $transaksi)
    {
        $this->otorisasiPetani($transaksi);

        abort_if($transaksi->status_transaksi !== 'pending', 403);

        // Kembalikan stok saat pesanan ditolak
        $transaksi->produk->increment('stok', $transaksi->jumlah);
        $transaksi->update(['status_transaksi' => 'batal']);

        return back()->with('success', 'Pesanan ditolak dan stok dikembalikan.');
    }

    // Pastikan petani hanya bisa aksi pesanan produknya sendiri
    private function otorisasiPetani(Transaksi $transaksi): void
    {
        abort_if($transaksi->produk->petani_id !== Auth::id(), 403);
    }
}
