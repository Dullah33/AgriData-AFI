<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UlasanRating;
use Illuminate\Http\Request;

class UlasanController extends Controller
{
    // GET /admin/ulasan
    // Daftar ulasan untuk dimoderasi Admin, dengan filter status
    // (aktif/dihapus) sesuai kolom `status` pada tabel ulasan_rating.
    public function index(Request $request)
    {
        $query = UlasanRating::with(['transaksi.pembeli', 'transaksi.produk'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ulasans = $query->paginate(10)->withQueryString();

        return view('admin.ulasan.index', [
            'ulasans' => $ulasans,
        ]);
    }

    // POST /admin/ulasan/{ulasanRating}/sembunyikan
    // Moderasi: sembunyikan ulasan yang melanggar (mis. konten kasar/spam)
    // tanpa menghapus data dari database.
    public function sembunyikan(UlasanRating $ulasan)
    {
        $ulasan->update(['status' => 'dihapus']);

        return back()->with('success', 'Ulasan berhasil disembunyikan.');
    }

    // POST /admin/ulasan/{ulasanRating}/aktifkan
    // Mengembalikan ulasan yang sebelumnya disembunyikan.
    public function aktifkan(UlasanRating $ulasan)
    {
        $ulasan->update(['status' => 'aktif']);

        return back()->with('success', 'Ulasan berhasil diaktifkan kembali.');
    }
}
