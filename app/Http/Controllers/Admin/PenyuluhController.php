<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExtensionOfficer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

        return view('Admin.penyuluh.index', [
            'penyuluhs' => $penyuluhs,
        ]);
    }

    // GET /admin/penyuluh/create
    // Form tambah akun Penyuluh baru. Sesuai BAB 2.2.2 & BAB 6.1, akun
    // Penyuluh TIDAK melalui registrasi publik — dibuat langsung oleh Admin
    // sekaligus penetapan wilayah binaan.
    public function create()
    {
        return view('Admin.penyuluh.create');
    }

    // POST /admin/penyuluh
    public function store(Request $request)
    {
        $data = $request->validate([
            'username'       => 'required|string|max:255|unique:users,username',
            'email'          => 'required|email|max:255|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'nip'            => 'nullable|string|max:30|unique:extension_officers,nip',
            'wilayah_binaan' => 'required|string|max:150',
            'phone'          => 'nullable|string|max:20',
        ], [
            'username.required'       => 'Username harus diisi.',
            'username.unique'         => 'Username sudah digunakan.',
            'email.required'          => 'Email harus diisi.',
            'email.unique'            => 'Email sudah terdaftar.',
            'password.required'       => 'Password harus diisi.',
            'password.min'            => 'Password minimal 8 karakter.',
            'password.confirmed'      => 'Konfirmasi password tidak cocok.',
            'nip.unique'               => 'NIP sudah terdaftar.',
            'wilayah_binaan.required' => 'Wilayah binaan harus diisi.',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'username' => $data['username'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => 'penyuluh',
            ]);

            ExtensionOfficer::create([
                'user_id'        => $user->id,
                'nip'            => $data['nip'] ?? null,
                'wilayah_binaan' => $data['wilayah_binaan'],
                'phone'          => $data['phone'] ?? null,
                'status'         => 'aktif',
                'assigned_by'    => Auth::id(),
            ]);
        });

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

        return view('Admin.penyuluh.show', [
            'penyuluh'         => $penyuluh,
            'kunjunganTerbaru' => $kunjunganTerbaru,
            'laporanBulanan'   => $laporanBulanan,
            'jumlahPetaniBinaan' => count($penyuluh->petaniBinaan()),
        ]);
    }

    // GET /admin/penyuluh/{penyuluh}/edit
    public function edit(ExtensionOfficer $penyuluh)
    {
        $penyuluh->load('user');

        return view('Admin.penyuluh.edit', [
            'penyuluh' => $penyuluh,
        ]);
    }

    // PUT /admin/penyuluh/{penyuluh}
    public function update(Request $request, ExtensionOfficer $penyuluh)
    {
        $data = $request->validate([
            'nip'            => 'nullable|string|max:30|unique:extension_officers,nip,' . $penyuluh->id,
            'wilayah_binaan' => 'required|string|max:150',
            'phone'          => 'nullable|string|max:20',
            'status'         => 'required|in:aktif,nonaktif',
        ], [
            'wilayah_binaan.required' => 'Wilayah binaan harus diisi.',
        ]);

        $penyuluh->update($data);

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
    public function destroy(ExtensionOfficer $penyuluh)
    {
        $user = $penyuluh->user;
        $penyuluh->delete();
        $user?->delete();

        return back()->with('success', 'Akun Penyuluh berhasil dihapus.');
    }
}
