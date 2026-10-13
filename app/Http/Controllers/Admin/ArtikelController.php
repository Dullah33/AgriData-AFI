<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArtikelRequest;
use App\Models\Artikel;
use App\Services\PenyimpananFoto;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    public function __construct(private PenyimpananFoto $foto) {}

    // GET /admin/artikel
    public function index(Request $request)
    {
        $artikels = Artikel::with('penulis')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->kategori))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.artikel.index', [
            'artikels'     => $artikels,
            'kategoriList' => Artikel::KATEGORI,
        ]);
    }

    // GET /admin/artikel/create
    public function create()
    {
        return view('admin.artikel.create', ['kategoriList' => Artikel::KATEGORI]);
    }

    // POST /admin/artikel
    public function store(ArtikelRequest $request)
    {
        $data = $request->safe()->except('foto_sampul') + ['user_id' => $request->user()->id];

        if ($request->hasFile('foto_sampul')) {
            $data['foto_sampul'] = $this->foto->ganti($request->file('foto_sampul'), 'artikel');
        }

        // Langsung dipublikasikan: catat waktu terbitnya
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        Artikel::create($data);

        return redirect()->route('admin.artikel.index')
            ->with('success', 'Artikel berhasil disimpan.');
    }

    // GET /admin/artikel/{artikel}/edit
    public function edit(Artikel $artikel)
    {
        return view('admin.artikel.edit', [
            'artikel'      => $artikel,
            'kategoriList' => Artikel::KATEGORI,
        ]);
    }

    // PUT /admin/artikel/{artikel}
    public function update(ArtikelRequest $request, Artikel $artikel)
    {
        $data = $request->safe()->except('foto_sampul');

        if ($request->hasFile('foto_sampul')) {
            $data['foto_sampul'] = $this->foto->ganti($request->file('foto_sampul'), 'artikel', $artikel->foto_sampul);
        }

        // Waktu terbit dicatat saat pertama kali dipublikasikan saja
        if ($data['status'] === 'published' && ! $artikel->published_at) {
            $data['published_at'] = now();
        }

        $artikel->update($data);

        return redirect()->route('admin.artikel.index')
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    // DELETE /admin/artikel/{artikel}
    public function destroy(Artikel $artikel)
    {
        $this->foto->hapus($artikel->foto_sampul);
        $artikel->delete();

        return back()->with('success', 'Artikel berhasil dihapus.');
    }
}
