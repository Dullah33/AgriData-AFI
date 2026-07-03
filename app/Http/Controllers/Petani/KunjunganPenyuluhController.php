<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class KunjunganPenyuluhController extends Controller
{
    // GET /petani/kunjungan-penyuluh
    // "Kunjungan Penyuluh" (BAB 2A.3): riwayat dan jadwal kunjungan
    // lapangan dari petugas penyuluh wilayahnya, termasuk rekomendasi
    // yang diberikan. Read-only dari sisi petani.
    public function index()
    {
        $petaniProfile = Auth::user()->petaniProfile;

        if (! $petaniProfile) {
            return view('petani.lahan.belum-profil');
        }

        $kunjungans = $petaniProfile->fieldVisits()
            ->with('extensionOfficer.user')
            ->latest('tanggal_kunjungan')
            ->paginate(10);

        return view('petani.kunjungan-penyuluh.index', [
            'kunjungans' => $kunjungans,
        ]);
    }
}
