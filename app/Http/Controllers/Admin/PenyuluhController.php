<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Penyuluh\BuatAkunPenyuluh;
use App\Actions\Penyuluh\HapusAkunPenyuluh;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanPenyuluhRequest;
use App\Models\ExtensionOfficer;
use Illuminate\Http\Request;

class PenyuluhController extends Controller
{
    // GET /admin/penyuluh
    // Manajemen Petugas Penyuluh (BAB 2A.2): tabel kelola akun Penyuluh,
    // tambah akun baru, penetapan wilayah binaan, ubah status.
    public function index(Request $request)
    {
        $query = ExtensionOfficer::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('wilayah_binaan', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('username', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $penyuluhs = $query->paginate(10)->withQueryString();

        return view('admin.penyuluh.index', [
            'penyuluhs' => $penyuluhs,
        ]);
    }

    // GET /admin/penyuluh/create
    // Form tambah akun Penyuluh baru. Sesuai BAB 2.2.2 & BAB 6.1, akun
    // Penyuluh TIDAK melalui registrasi publik — dibuat langsung oleh Admin
    // sekaligus penetapan wilayah binaan.
    public function create()
    {
        return view('admin.penyuluh.create');
    }

    // POST /admin/penyuluh
    public function store(SimpanPenyuluhRequest $request, BuatAkunPenyuluh $buatAkun)
    {
        $buatAkun($request->validated(), $request->user());

        return redirect()->route('admin.penyuluh.index')
            ->with('success', 'Akun Petugas Penyuluh berhasil dibuat.');
    }

    // GET /admin/penyuluh/{penyuluh}
    // Detail Profil Penyuluh (Admin) (BAB 2A.2): wilayah binaan, riwayat
    // kunjungan, dan kinerja bulanan.
    public function show(ExtensionOfficer $penyuluh)
    {
        $penyuluh->load(['user', 'assignedBy']);

        $kunjunganTerbaru = $penyuluh->fieldVisits()
            ->with('petaniProfile.user')
            ->latest('tanggal_kunjungan')
            ->take(10)
            ->get();

        $laporanBulanan = $penyuluh->monthlyReports()
            ->latest('tahun')->latest('bulan')
            ->take(6)
            ->get();

        return view('admin.penyuluh.show', [
            'penyuluh'         => $penyuluh,
            'kunjunganTerbaru' => $kunjunganTerbaru,
            'laporanBulanan'   => $laporanBulanan,
            // COUNT di database, bukan memuat semua petani lalu dihitung di PHP
            'jumlahPetaniBinaan' => $penyuluh->petaniBinaanQuery()->count(),
        ]);
    }

    // GET /admin/penyuluh/{penyuluh}/edit
    public function edit(ExtensionOfficer $penyuluh)
    {
        $penyuluh->load('user');

        return view('admin.penyuluh.edit', [
            'penyuluh' => $penyuluh,
        ]);
    }

    // PUT /admin/penyuluh/{penyuluh}
    public function update(SimpanPenyuluhRequest $request, ExtensionOfficer $penyuluh)
    {
        $penyuluh->update($request->validated());

        return redirect()->route('admin.penyuluh.index')
            ->with('success', 'Data Penyuluh berhasil diperbarui.');
    }

    // POST /admin/penyuluh/{penyuluh}/nonaktifkan
    public function nonaktifkan(ExtensionOfficer $penyuluh)
    {
        $penyuluh->update(['status' => 'nonaktif']);

        return back()->with('success', 'Akun Penyuluh dinonaktifkan.');
    }

    // POST /admin/penyuluh/{penyuluh}/aktifkan
    public function aktifkan(ExtensionOfficer $penyuluh)
    {
        $penyuluh->update(['status' => 'aktif']);

        return back()->with('success', 'Akun Penyuluh diaktifkan kembali.');
    }

    // DELETE /admin/penyuluh/{penyuluh}
    // Menghapus akun Penyuluh sekaligus akun user-nya (cascade).
    public function destroy(ExtensionOfficer $penyuluh, HapusAkunPenyuluh $hapusAkun)
    {
        $hapusAkun($penyuluh);

        return back()->with('success', 'Akun Penyuluh berhasil dihapus.');
    }
}