<?php

namespace App\Http\Controllers\Penyuluh;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class WilayahBinaanController extends Controller
{
    // GET /penyuluh/wilayah-binaan
    // Halaman "Wilayah Binaan" (BAB 2A.6): peta & daftar desa/kecamatan
    // yang menjadi tanggung jawab penyuluh, beserta daftar petani di
    // dalamnya. Layer peta menyusul setelah data koordinat wilayah lengkap;
    // untuk sekarang fokus pada daftar petani binaan (data yang sudah ada).
    public function index()
    {
        $officer = Auth::user()->extensionOfficer;

        if (! $officer) {
            return view('penyuluh.belum-terdaftar');
        }

        $petaniBinaan = $officer->petaniBinaan();

        return view('penyuluh.wilayah-binaan.index', [
            'officer'      => $officer,
            'petaniBinaan' => $petaniBinaan,
        ]);
    }
}
