<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lahan;
use App\Models\Wilayah;

class PetaLahanController extends Controller
{
    // GET /admin/peta-lahan
    // "Manajemen Peta Lahan & Wilayah" (BAB 2A.2 & 2.3.2): visualisasi
    // poligon lahan tiap petani plus marker wilayah binaan. Poligon
    // diambil dari kolom lahan.koordinat_poligon yang diisi petani lewat
    // widget Leaflet.draw di halaman "Profil & Lahan Saya".
    public function index()
    {
        $wilayahList = Wilayah::withCount('petaniProfiles')->get();

        $markerWilayah = $wilayahList->map(fn (Wilayah $w) => [
            'nama_wilayah'  => $w->nama_wilayah,
            'latitude'      => $w->latitude,
            'longitude'     => $w->longitude,
            'jumlah_petani' => $w->petani_profiles_count,
        ])->filter(fn ($w) => $w['latitude'] && $w['longitude'])->values();

        $lahans = Lahan::with('petaniProfile.user')
            ->whereNotNull('koordinat_poligon')
            ->get()
            ->filter(fn (Lahan $l) => is_array($l->koordinat_poligon) && count($l->koordinat_poligon) >= 3)
            ->map(fn (Lahan $l) => [
                'nama_lahan'     => $l->nama_lahan,
                'petani'         => $l->petaniProfile->user->username ?? '-',
                'tanaman_aktif'  => $l->tanaman_aktif,
                'luas_ha'        => $l->luas_ha,
                'status'         => $l->status,
                'koordinat'      => $l->koordinat_poligon,
            ])->values();

        return view('admin.peta-lahan.index', [
            'wilayahList'   => $wilayahList,
            'markerWilayah' => $markerWilayah,
            'poligonLahan'  => $lahans,
        ]);
    }
}
