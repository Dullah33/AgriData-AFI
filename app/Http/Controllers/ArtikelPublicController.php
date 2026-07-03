<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Http\Request;

class ArtikelPublicController extends Controller
{
    private array $kategoriList = [
        'Teknik Budidaya',
        'Hama & Penyakit',
        'Cuaca',
        'Teknologi Pertanian',
        'Kebijakan',
    ];

    // GET /artikel
    // "Artikel Pertanian" (BAB 2A.3 & 2A.4): daftar artikel edukatif,
    // read-only, dipakai bersama oleh role Petani & User (dan siapa pun
    // yang login) — hanya artikel berstatus 'published' yang tampil.
    public function index(Request $request)
    {
        $query = Artikel::published()->with('penulis')->latest('published_at');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('cari')) {
            $query->where('judul', 'like', '%' . $request->cari . '%');
        }

        $artikels = $query->paginate(9)->withQueryString();

        return view('artikel.index', [
            'artikels'     => $artikels,
            'kategoriList' => $this->kategoriList,
        ]);
    }

    // GET /artikel/{artikel:slug}
    public function show(Artikel $artikel)
    {
        abort_unless($artikel->status === 'published', 404);

        $terkait = Artikel::published()
            ->where('kategori', $artikel->kategori)
            ->where('id', '!=', $artikel->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('artikel.show', [
            'artikel' => $artikel,
            'terkait' => $terkait,
        ]);
    }
}
