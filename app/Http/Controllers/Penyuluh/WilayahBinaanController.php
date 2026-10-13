<?php

namespace App\Http\Controllers\Penyuluh;

use App\Http\Controllers\Concerns\MembutuhkanPenyuluh;
use App\Http\Controllers\Controller;

class WilayahBinaanController extends Controller
{
    use MembutuhkanPenyuluh;

    // GET /penyuluh/wilayah-binaan
    // "Wilayah Binaan" (BAB 2A.6): daftar petani di wilayah tanggung jawab penyuluh.
    public function index()
    {
        if (! $officer = $this->penyuluhAtauNull()) {
            return view('penyuluh.belum-terdaftar');
        }

        return view('penyuluh.wilayah-binaan.index', [
            'officer'      => $officer,
            'petaniBinaan' => $officer->petaniBinaan(),
        ]);
    }
}
