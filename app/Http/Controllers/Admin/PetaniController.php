<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PetaniProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PetaniController extends Controller
{
    // GET /admin/petani
    // Daftar seluruh profil petani, dengan filter status verifikasi
    // sesuai dokumen modul (verifikasi akun petani oleh Admin).
    public function index(Request $request)
    {
        $query = PetaniProfile::with(['user', 'wilayah'])->latest();

        if ($request->filled('status')) {
            if ($request->status === 'terverifikasi') {
                $query->terverifikasi();
            } elseif ($request->status === 'belum') {
                $query->belumTerverifikasi();
            }
        }

        $petaniProfiles = $query->paginate(10)->withQueryString();

        return view('admin.petani.index', [
            'petaniProfiles' => $petaniProfiles,
        ]);
    }

    // GET /admin/petani/{petaniProfile}
    // Detail satu profil petani (NIK, alamat, foto KTP, dll.) sebelum diverifikasi.
    public function show(PetaniProfile $petani)
    {
        $petani->load(['user', 'wilayah', 'verifikator']);

        return view('admin.petani.show', [
            'petani' => $petani,
        ]);
    }

    // POST /admin/petani/{petaniProfile}/verifikasi
    public function verifikasi(PetaniProfile $petani)
    {
        $petani->update(['verified_by' => Auth::id()]);

        return back()->with('success', 'Akun petani berhasil diverifikasi.');
    }

    // POST /admin/petani/{petaniProfile}/batalkan-verifikasi
    // Untuk membatalkan verifikasi kalau ternyata data petani bermasalah.
    public function batalkanVerifikasi(PetaniProfile $petani)
    {
        $petani->update(['verified_by' => null]);

        return back()->with('success', 'Verifikasi akun petani dibatalkan.');
    }
}
