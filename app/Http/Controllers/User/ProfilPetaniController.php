<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PetaniProfile;
use App\Models\ProdukPanen;
use Illuminate\Http\Request;

class ProfilPetaniController extends Controller
{
    // GET /profil-petani
    // "Profil Petani (Lihat)" (BAB 2A.4): daftar petani aktif & terverifikasi
    // yang bisa dilihat masyarakat umum, read-only.
    public function index(Request $request)
    {
        $query = PetaniProfile::with(['user', 'wilayah'])
            ->terverifikasi()
            ->where('status_aktif', true);

        if ($request->filled('cari')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->cari . '%');
            });
        }

        $petanis = $query->paginate(12)->withQueryString();

        return view('user.petani.index', [
            'petanis' => $petanis,
        ]);
    }

    // GET /profil-petani/{petani}
    public function show(PetaniProfile $petani)
    {
        abort_unless($petani->isTerverifikasi() && $petani->status_aktif, 404);

        $produk = ProdukPanen::where('petani_id', $petani->user_id)
            ->tersedia()
            ->latest()
            ->get();

        return view('user.petani.show', [
            'petani' => $petani,
            'produk' => $produk,
        ]);
    }
}
