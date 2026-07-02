<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\ProdukPanen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProdukPanenController extends Controller
{
    private array $kategoriList = [
        'Sayuran', 'Buah-buahan', 'Biji-bijian',
        'Umbi-umbian', 'Rempah-rempah', 'Lainnya',
    ];

    // GET /petani/produk
    public function index()
    {
        // FIX: filter out 'dihapus' — hanya tampilkan tersedia & habis
        $produk = ProdukPanen::where('petani_id', Auth::id())
            ->whereIn('status', ['tersedia', 'habis'])
            ->latest()->paginate(10);

        return view('petani.produk.index', compact('produk'));
    }

    // GET /petani/produk/create
    public function create()
    {
        return view('petani.produk.create', [
            'kategoriList' => $this->kategoriList,
        ]);
    }

    // POST /petani/produk
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_produk' => 'required|string|max:150',
            'kategori'    => 'required|string|max:50',
            'deskripsi'   => 'required|string',
            'harga'       => 'required|numeric|min:0',
            'satuan'      => 'required|string|max:20',
            'stok'        => 'required|integer|min:0',
            'foto_produk' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nama_produk.required' => 'Nama produk harus diisi.',
            'kategori.required'    => 'Kategori harus dipilih.',
            'deskripsi.required'   => 'Deskripsi harus diisi.',
            'harga.required'       => 'Harga harus diisi.',
            'stok.required'        => 'Stok harus diisi.',
            'foto_produk.image'    => 'File harus berupa gambar.',
            'foto_produk.max'      => 'Ukuran foto maksimal 2MB.',
        ]);

        $data['petani_id'] = Auth::id();
        $data['status']    = 'tersedia';

        if ($request->hasFile('foto_produk')) {
            $data['foto_produk'] = $request->file('foto_produk')
                ->store('produk', 'public');
        }

        ProdukPanen::create($data);

        return redirect()->route('petani.produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    // GET /petani/produk/{id}/edit
    public function edit(ProdukPanen $produk)
    {
        abort_if($produk->petani_id !== Auth::id(), 403);

        return view('petani.produk.edit', [
            'produk'       => $produk,
            'kategoriList' => $this->kategoriList,
        ]);
    }

    // PUT /petani/produk/{id}
    public function update(Request $request, ProdukPanen $produk)
    {
        abort_if($produk->petani_id !== Auth::id(), 403);

        $data = $request->validate([
            'nama_produk' => 'required|string|max:150',
            'kategori'    => 'required|string|max:50',
            'deskripsi'   => 'required|string',
            'harga'       => 'required|numeric|min:0',
            'satuan'      => 'required|string|max:20',
            'stok'        => 'required|integer|min:0',
            'status'      => 'required|in:tersedia,habis',
            'foto_produk' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('foto_produk')) {
            if ($produk->foto_produk) {
                Storage::disk('public')->delete($produk->foto_produk);
            }
            $data['foto_produk'] = $request->file('foto_produk')
                ->store('produk', 'public');
        }

        $produk->update($data);

        return redirect()->route('petani.produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    // DELETE /petani/produk/{id}
    public function destroy(ProdukPanen $produk)
    {
        abort_if($produk->petani_id !== Auth::id(), 403);

        if ($produk->foto_produk) {
            Storage::disk('public')->delete($produk->foto_produk);
        }

        // Soft delete via status — data transaksi terkait tetap aman
        $produk->update(['status' => 'dihapus']);

        return back()->with('success', 'Produk berhasil dihapus.');
    }
}