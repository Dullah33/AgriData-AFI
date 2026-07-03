<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard sesuai role user yang sedang login.
     *
     * Satu route /dashboard untuk semua role, tapi view yang dirender
     * berbeda-beda tergantung kolom `role` pada user. Pendekatan ini
     * dipilih supaya link "Dashboard" di mana pun selalu konsisten
     * mengarah ke /dashboard, tanpa perlu tahu role user di awal.
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->role;

        return match ($role) {
            'admin'    => view('dashboard.admin'),
            'petani'   => view('dashboard.petani'),
            'penyuluh' => $this->dashboardPenyuluh($user),
            'user'     => view('dashboard.user'),
            default    => abort(403, 'Role tidak dikenali.'),
        };
    }

    // Dashboard Penyuluh (BAB 2A.6): ringkasan jumlah petani binaan,
    // kunjungan bulan ini, dan laporan penyakit terbaru di wilayah binaan.
    // Bagian "laporan penyakit" belum diisi karena AI Scanner/disease_reports
    // masih dikerjakan terpisah.
    private function dashboardPenyuluh($user)
    {
        $officer = $user->extensionOfficer;

        if (! $officer) {
            return view('dashboard.penyuluh', [
                'officer' => null,
            ]);
        }

        return view('dashboard.penyuluh', [
            'officer'            => $officer,
            'jumlahPetaniBinaan' => count($officer->petaniBinaan()),
            'kunjunganBulanIni'  => $officer->fieldVisits()->bulanIni()->count(),
            'kunjunganTerjadwal' => $officer->fieldVisits()->terjadwal()->count(),
            'pelatihanBulanIni'  => $officer->trainingPrograms()->bulanIni()->count(),
            'kunjunganTerbaru'   => $officer->fieldVisits()->with('petaniProfile.user')->latest('tanggal_kunjungan')->take(5)->get(),
        ]);
    }
}