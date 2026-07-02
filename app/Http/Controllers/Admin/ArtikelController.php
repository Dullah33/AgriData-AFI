<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class ArtikelController extends Controller
{
    // Daftar kategori artikel (sesuai dokumen modul BAB 2.3.4)
    private array $kategoriList = [
        'Teknik Budidaya',
        'Hama & Penyakit',
        'Cuaca',
        'Teknologi Pertanian',
        'Kebijakan',
    ];

    // GET /admin/artikel
    public function index(Request $request)
    {
        $query = Artikel::with('penulis')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $artikels = $query->paginate(10)->withQueryString();

        return view('admin.artikel.index', [
            'artikels'     => $artikels,
            'kategoriList' => $this->kategoriList,
        ]);
    }

    // GET /admin/artikel/create
    public function create()
    {
        return view('admin.artikel.create', [
            'kategoriList' => $this->kategoriList,
        ]);
    }

    // POST /admin/artikel
    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'      => 'required|string|max:200',
            'kategori'   => 'required|string|max:50',
            'konten'     => 'required|string',
            'foto_sampul' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'     => 'required|in:draft,published',
        ], [
            'judul.required'    => 'Judul artikel harus diisi.',
            'kategori.required' => 'Kategori harus dipilih.',
            'konten.required'   => 'Isi artikel harus diisi.',
            'foto_sampul.image' => 'File harus berupa gambar.',
            'foto_sampul.max'   => 'Ukuran foto maksimal 2MB.',
            'status.required'   => 'Status publikasi harus dipilih.',
        ]);

        $data['user_id'] = Auth::id();

        // Upload foto sampul jika ada
        if ($request->hasFile('foto_sampul')) {
            $data['foto_sampul'] = $request->file('foto_sampul')
                ->store('artikel', 'public');
        }

        // Set published_at otomatis jika langsung dipublish
        if ($data['status'] === 'published') {
            $data['published_at'] = Carbon::now();
        }

        Artikel::create($data);

        return redirect()->route('admin.artikel.index')
            ->with('success', 'Artikel berhasil disimpan.');
    }

    // GET /admin/artikel/{id}/edit
    public function edit(Artikel $artikel)
    {
        return view('admin.artikel.edit', [
            'artikel'      => $artikel,
            'kategoriList' => $this->kategoriList,
        ]);
    }

    // PUT /admin/artikel/{id}
    public function update(Request $request, Artikel $artikel)
    {
        $data = $request->validate([
            'judul'      => 'required|string|max:200',
            'kategori'   => 'required|string|max:50',
            'konten'     => 'required|string',
            'foto_sampul' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'     => 'required|in:draft,published',
        ]);

        // Upload foto baru jika ada, hapus yang lama
        if ($request->hasFile('foto_sampul')) {
            if ($artikel->foto_sampul) {
                Storage::disk('public')->delete($artikel->foto_sampul);
            }
            $data['foto_sampul'] = $request->file('foto_sampul')
                ->store('artikel', 'public');
        }

        // Set published_at saat pertama kali dipublish
        if ($data['status'] === 'published' && ! $artikel->published_at) {
            $data['published_at'] = Carbon::now();
        }

        $artikel->update($data);

        return redirect()->route('admin.artikel.index')
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    // DELETE /admin/artikel/{id}
    public function destroy(Artikel $artikel)
    {
        if ($artikel->foto_sampul) {
            Storage::disk('public')->delete($artikel->foto_sampul);
        }

        $artikel->delete();

        return back()->with('success', 'Artikel berhasil dihapus.');
    }
}
