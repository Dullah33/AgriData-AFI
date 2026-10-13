<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KeranjangItem;
use App\Models\Pembayaran;
use App\Models\ProdukPanen;
use App\Models\Transaksi;
use App\Services\Marketplace\PembayaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    // GET /marketplace
    public function index(Request $request)
    {
        $query = ProdukPanen::with('petani')->tersedia()->latest();

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('cari')) {
            $query->where('nama_produk', 'like', '%' . $request->cari . '%');
        }

        $produks  = $query->paginate(12)->withQueryString();
        $kategoris = ProdukPanen::tersedia()->distinct()->pluck('kategori')->filter()->sort();

        return view('user.marketplace.index', compact('produks', 'kategoris'));
    }

    // GET /marketplace/{id}
    public function show(ProdukPanen $produk)
    {
        abort_if($produk->status !== 'tersedia', 404);

        $produk->load('petani');
        $diKeranjang = KeranjangItem::where('user_id', Auth::id())->where('produk_id', $produk->id)->value('jumlah') ?? 0;

        return view('user.marketplace.show', compact('produk', 'diKeranjang'));
    }

    // GET /pesanan-saya
    // "Pesanan Saya" (BAB 2A.4): daftar tagihan hasil checkout beserta status
    // pembayaran dan status tiap produknya. Pesanan yang sudah selesai juga
    // muncul di "Riwayat & Ulasan Saya" untuk diberi ulasan.
    public function pesananSaya(PembayaranService $layanan)
    {
        $layanan->kedaluwarsakanJatuhTempo(Auth::id());

        $pembayarans = Pembayaran::with('transaksis.produk.petani')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(8);

        // Transaksi lama yang dibuat sebelum ada fitur pembayaran
        $pesananLama = Transaksi::with('produk.petani')
            ->where('user_id', Auth::id())
            ->whereNull('pembayaran_id')
            ->whereIn('status_transaksi', [Transaksi::MENUNGGU, Transaksi::DIPROSES])
            ->latest()
            ->get();

        return view('user.marketplace.pesanan', compact('pembayarans', 'pesananLama'));
    }

    // GET /riwayat-ulasan
    // "Riwayat & Ulasan Saya" (BAB 2A.4): pesanan yang SUDAH selesai,
    // dengan status ulasan (sudah/belum diulas).
    public function riwayatUlasan()
    {
        $pesanans = Transaksi::with(['produk.petani', 'ulasan'])
            ->where('user_id', Auth::id())
            ->where('status_transaksi', Transaksi::SELESAI)
            ->latest()
            ->paginate(10);

        return view('user.marketplace.riwayat-ulasan', compact('pesanans'));
    }
}