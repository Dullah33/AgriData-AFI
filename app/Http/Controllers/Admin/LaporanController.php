<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PetaniProfile;
use App\Models\Transaksi;
use App\Services\Ekspor\EksporCsv;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    // GET /admin/laporan
    // "Laporan & Ekspor Data" (BAB 2A.2): pemilihan jenis laporan dan
    // ekspor ke CSV (dibuka otomatis oleh Excel/Google Sheets). Ekspor PDF
    // butuh package tambahan yang belum terpasang di project ini.
    public function index()
    {
        return view('admin.laporan.index', [
            'jumlahPetani'    => PetaniProfile::count(),
            'jumlahTransaksi' => Transaksi::count(),
        ]);
    }

    // GET /admin/laporan/petani/export
    public function exportPetani(EksporCsv $csv): StreamedResponse
    {
        $baris = PetaniProfile::with(['user', 'wilayah'])->lazyById(200)->map(fn (PetaniProfile $p) => [
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

        return $csv->unduh('laporan-petani-'.now()->format('Y-m-d').'.csv', [
            'Username', 'Email', 'NIK', 'Kelompok Tani', 'Wilayah',
            'Komoditas Utama', 'Luas Lahan (Ha)', 'Status Aktif',
            'Status Verifikasi', 'Tanggal Daftar',
        ], $baris);
    }

    // GET /admin/laporan/transaksi/export
    public function exportTransaksi(EksporCsv $csv): StreamedResponse
    {
        $baris = Transaksi::with(['pembeli', 'produk.petani'])->lazyById(200)->map(fn (Transaksi $t) => [
            $t->id,
            $t->pembeli->username ?? '-',
            $t->produk->nama_produk ?? '-',
            $t->produk->petani->username ?? '-',
            $t->jumlah,
            $t->total_harga,
            $t->labelStatus(),
            $t->created_at?->format('d-m-Y H:i'),
        ]);

        return $csv->unduh('laporan-transaksi-'.now()->format('Y-m-d').'.csv', [
            'ID Transaksi', 'Pembeli', 'Produk', 'Petani Penjual',
            'Jumlah', 'Total Harga', 'Status', 'Tanggal Transaksi',
        ], $baris);
    }
}
