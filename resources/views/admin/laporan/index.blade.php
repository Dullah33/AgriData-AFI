@extends('layouts.dashboard')

@section('title', 'Laporan & Ekspor Data')

@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-1">Laporan & Ekspor Data</h2>
    <p class="text-sm text-gray-500">
        Pilih jenis data yang ingin diekspor. File akan diunduh dalam format CSV
        (kompatibel dengan Excel/Google Sheets).
    </p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    {{-- Laporan Petani --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-800">Data Petani</h3>
            <span class="text-xs text-gray-400">{{ $jumlahPetani }} baris</span>
        </div>
        <p class="text-sm text-gray-500 mb-4">
            Username, NIK, kelompok tani, wilayah, komoditas utama, luas lahan,
            status aktif &amp; verifikasi.
        </p>
        <a href="{{ route('admin.laporan.petani.export') }}"
           class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
            ⬇ Ekspor CSV
        </a>
    </div>

    {{-- Laporan Transaksi --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-800">Data Transaksi</h3>
            <span class="text-xs text-gray-400">{{ $jumlahTransaksi }} baris</span>
        </div>
        <p class="text-sm text-gray-500 mb-4">
            Pembeli, produk, petani penjual, jumlah, total harga, status, dan
            tanggal transaksi.
        </p>
        <a href="{{ route('admin.laporan.transaksi.export') }}"
           class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
            ⬇ Ekspor CSV
        </a>
    </div>
</div>

<p class="text-xs text-gray-400 mt-6">
    Catatan: ekspor PDF belum tersedia karena butuh package tambahan
    (barryvdh/laravel-dompdf) yang belum terpasang di project ini.
</p>

@endsection
