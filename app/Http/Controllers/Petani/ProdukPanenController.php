<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Http\Requests\Petani\ProdukPanenRequest;
use App\Models\ProdukPanen;
use App\Services\PenyimpananFoto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProdukPanenController extends Controller
{
    public function __construct(private PenyimpananFoto $foto) {}

    // GET /petani/produk
    public function index()
    {
        $produk = ProdukPanen::where('petani_id', Auth::id())
            ->terlihat()
            ->latest()
            ->paginate(10);

        return view('petani.produk.index', compact('produk'));
    }

    // GET /petani/produk/create
    public function create()
    {
        return view('petani.produk.create', ['kategoriList' => ProdukPanen::KATEGORI]);
    }

    // POST /petani/produk
    public function store(ProdukPanenRequest $request)
    {
        $data = $request->safe()->except('foto_produk') + [
            'petani_id' => $request->user()->id,
            'status'    => 'tersedia',
        ];

        if ($request->hasFile('foto_produk')) {
            $data['foto_produk'] = $this->foto->ganti($request->file('foto_produk'), 'produk');
        }

        ProdukPanen::create($data);

        return redirect()->route('petani.produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    // GET /petani/produk/{produk}/edit
    public function edit(ProdukPanen $produk)
    {
        Gate::authorize('update', $produk);

        return view('petani.produk.edit', [
            'produk'       => $produk,
            'kategoriList' => ProdukPanen::KATEGORI,
        ]);
    }

    // PUT /petani/produk/{produk}
    public function update(ProdukPanenRequest $request, ProdukPanen $produk)
    {
        Gate::authorize('update', $produk);

        $data = $request->safe()->except('foto_produk');

        if ($request->hasFile('foto_produk')) {
            $data['foto_produk'] = $this->foto->ganti($request->file('foto_produk'), 'produk', $produk->foto_produk);
        }

        $produk->update($data);

        return redirect()->route('petani.produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    // DELETE /petani/produk/{produk}
    // Hapus lunak lewat status. Foto sengaja tidak dihapus karena riwayat
    // pesanan pembeli masih menampilkan produk ini.
    public function destroy(ProdukPanen $produk)
    {
        Gate::authorize('delete', $produk);

        $produk->update(['status' => 'dihapus']);

        return back()->with('success', 'Produk berhasil dihapus.');
    }
}
