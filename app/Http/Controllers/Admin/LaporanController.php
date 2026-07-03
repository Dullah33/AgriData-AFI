<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PetaniProfile;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    // GET /admin/laporan
    // "Laporan & Ekspor Data" (BAB 2A.2): halaman pemilihan jenis laporan
    // dan ekspor ke Excel. Diekspor sebagai CSV (native PHP, tanpa
    // dependency tambahan) — CSV dibuka otomatis oleh Excel/Google
    // Sheets, jadi fungsinya setara. Ekspor PDF butuh package tambahan
    // (mis. barryvdh/laravel-dompdf) yang belum terpasang di project ini.
    public function index()
    {
        return view('admin.laporan.index', [
            'jumlahPetani'    => PetaniProfile::count(),
            'jumlahTransaksi' => Transaksi::count(),
        ]);
    }

    // GET /admin/laporan/petani/export
    public function exportPetani(): StreamedResponse
    {
        $filename = 'laporan-petani-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            // BOM supaya karakter/emoji terbaca benar saat dibuka di Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Username', 'Email', 'NIK', 'Kelompok Tani', 'Wilayah',
                'Komoditas Utama', 'Luas Lahan (Ha)', 'Status Aktif',
                'Status Verifikasi', 'Tanggal Daftar',
            ]);

            PetaniProfile::with(['user', 'wilayah'])->chunk(200, function ($petanis) use ($handle) {
                foreach ($petanis as $p) {
                    fputcsv($handle, [
                        $p->user->username ?? '-',
                        $p->user->email ?? '-',
                        $p->nik ?? '-',
                        $p->nama_kelompok_tani ?? '-',
                        $p->wilayah->nama_wilayah ?? '-',
                        $p->komoditas_utama ?? '-',
                        $p->luas_lahan,
                        $p->status_aktif ? 'Aktif' : 'Nonaktif',
                        $p->isTerverifikasi() ? 'Terverifikasi' : 'Belum Diverifikasi',
                        $p->created_at?->format('d-m-Y'),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // GET /admin/laporan/transaksi/export
    public function exportTransaksi(): StreamedResponse
    {
        $filename = 'laporan-transaksi-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID Transaksi', 'Pembeli', 'Produk', 'Petani Penjual',
                'Jumlah', 'Total Harga', 'Status', 'Tanggal Transaksi',
            ]);

            Transaksi::with(['pembeli', 'produk.petani'])->chunk(200, function ($transaksis) use ($handle) {
                foreach ($transaksis as $t) {
                    fputcsv($handle, [
                        $t->id,
                        $t->pembeli->username ?? '-',
                        $t->produk->nama_produk ?? '-',
                        $t->produk->petani->username ?? '-',
                        $t->jumlah,
                        $t->total_harga,
                        ucfirst($t->status_transaksi),
                        $t->created_at?->format('d-m-Y H:i'),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
