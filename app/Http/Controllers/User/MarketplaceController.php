<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProdukPanen;
use App\Models\Transaksi;
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
        return view('user.marketplace.show', compact('produk'));
    }

    // POST /marketplace/{id}/beli
    public function beli(Request $request, ProdukPanen $produk)
    {
        abort_if($produk->status !== 'tersedia' || $produk->stok < 1, 422, 'Produk tidak tersedia.');

        $data = $request->validate([
            'jumlah'            => 'required|integer|min:1|max:' . $produk->stok,
            'alamat_pengiriman' => 'required|string|max:500',
            'catatan'           => 'nullable|string|max:300',
        ], [
            'jumlah.required'            => 'Jumlah harus diisi.',
            'jumlah.max'                 => 'Jumlah melebihi stok tersedia (' . $produk->stok . ').',
            'alamat_pengiriman.required' => 'Alamat pengiriman harus diisi.',
        ]);

        $total = $produk->harga * $data['jumlah'];

        Transaksi::create([
            'user_id'           => Auth::id(),
            'produk_id'         => $produk->id,
            'jumlah'            => $data['jumlah'],
            'total_harga'       => $total,
            'status_transaksi'  => 'pending',
            'alamat_pengiriman' => $data['alamat_pengiriman'],
            'catatan'           => $data['catatan'] ?? null,
        ]);

        // Kurangi stok setelah order dibuat
        $produk->decrement('stok', $data['jumlah']);

        return redirect()->route('user.pesanan')
            ->with('success', 'Pesanan berhasil dibuat! Menunggu konfirmasi petani.');
    }

    // GET /pesanan-saya (riwayat pesanan user)
    public function pesananSaya()
    {
        $pesanans = Transaksi::with(['produk.petani', 'ulasan'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('user.marketplace.pesanan', compact('pesanans'));
    }
}
