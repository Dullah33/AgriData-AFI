<?php

namespace App\Http\Controllers;

use App\Models\ExtensionOfficer;
use App\Models\PetaniProfile;
use App\Models\ProdukPanen;
use App\Models\Transaksi;
use App\Models\Artikel;
use App\Models\UlasanRating;
use App\Models\FieldVisit;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;

        return match ($role) {
            'admin'    => $this->adminDashboard(),
            'petani'   => $this->petaniDashboard(),
            'penyuluh' => $this->penyuluhDashboard(),
            'user'     => $this->userDashboard(),
            default    => abort(403, 'Role tidak dikenali.'),
        };
    }

    private function adminDashboard()
    {
        $stats = [
            'total_petani'        => PetaniProfile::count(),
            'petani_terverifikasi' => PetaniProfile::whereNotNull('verified_by')->count(),
            'transaksi_bulan_ini' => Transaksi::whereMonth('created_at', now()->month)->count(),
            'transaksi_pending'   => Transaksi::where('status_transaksi', 'pending')->count(),
            'penyuluh_aktif'      => ExtensionOfficer::where('status', 'aktif')->count(),
            'artikel_published'   => Artikel::where('status', 'published')->count(),
            'artikel_draft'       => Artikel::where('status', 'draft')->count(),
        ];

        $petaniBelumVerifikasi = PetaniProfile::with('user')
            ->whereNull('verified_by')->latest()->take(5)->get();

        $ulasanTerbaru = UlasanRating::where('status', 'aktif')
            ->latest()->take(5)->get();

        return view('dashboard.admin', compact('stats', 'petaniBelumVerifikasi', 'ulasanTerbaru'));
    }

    private function petaniDashboard()
    {
        $user    = Auth::user();
        $profile = PetaniProfile::where('user_id', $user->id)->first();

        $produkIds = ProdukPanen::where('petani_id', $user->id)->pluck('id');

        $stats = [
            'produk_aktif'        => ProdukPanen::where('petani_id', $user->id)->where('status', 'tersedia')->count(),
            'pesanan_pending'     => Transaksi::whereIn('produk_id', $produkIds)->where('status_transaksi', 'pending')->count(),
            'total_lahan'         => $profile?->lahans()->count() ?? 0,
            'kunjungan_terjadwal' => $profile ? FieldVisit::where('petani_profile_id', $profile->id)->where('status', 'terjadwal')->count() : 0,
        ];

        $pesananTerbaru = Transaksi::with(['produk', 'pembeli'])
            ->whereIn('produk_id', $produkIds)
            ->where('status_transaksi', 'pending')
            ->latest()->take(5)->get();

        return view('dashboard.petani', compact('stats', 'pesananTerbaru'));
    }

    private function userDashboard()
    {
        $userId = Auth::id();

        $stats = [
            'pesanan_aktif'   => Transaksi::where('user_id', $userId)->whereIn('status_transaksi', ['pending', 'diproses'])->count(),
            'pesanan_selesai' => Transaksi::where('user_id', $userId)->where('status_transaksi', 'selesai')->count(),
            'belum_diulas'    => Transaksi::where('user_id', $userId)->where('status_transaksi', 'selesai')
                                    ->whereDoesntHave('ulasan')->count(),
        ];

        $pesananTerbaru = Transaksi::with('produk')
            ->where('user_id', $userId)->latest()->take(5)->get();

        $produkTerbaru = ProdukPanen::with('petani')->tersedia()->latest()->take(5)->get();

        return view('dashboard.user', compact('stats', 'pesananTerbaru', 'produkTerbaru'));
    }

    private function penyuluhDashboard()
    {
        $officer = ExtensionOfficer::where('user_id', Auth::id())->first();

        $stats = [
            'petani_binaan'       => $officer ? count($officer->petaniBinaan()) : 0,
            'kunjungan_bulan_ini' => $officer ? $officer->fieldVisits()->bulanIni()->where('status', 'selesai')->count() : 0,
            'kunjungan_terjadwal' => $officer ? $officer->fieldVisits()->where('status', 'terjadwal')->count() : 0,
            'pelatihan_bulan_ini' => $officer ? $officer->trainingPrograms()->bulanIni()->count() : 0,
        ];

        $kunjunganMendatang = $officer
            ? FieldVisit::where('extension_officer_id', $officer->id)
                ->where('status', 'terjadwal')
                ->where('tanggal_kunjungan', '>=', now()->toDateString())
                ->with('petaniProfile.user')
                ->orderBy('tanggal_kunjungan')->take(5)->get()
            : collect();

        return view('dashboard.penyuluh', compact('officer', 'stats', 'kunjunganMendatang'));
    }
}