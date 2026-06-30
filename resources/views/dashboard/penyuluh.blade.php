@extends('layouts.dashboard')

@section('title', 'Dashboard Penyuluh')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Selamat datang, {{ Auth::user()->username }} 👋</h2>
        <p class="text-gray-500 text-sm">
            Ini halaman Dashboard Penyuluh. Ringkasan jumlah petani binaan, kunjungan
            bulan ini, dan laporan penyakit terbaru akan diisi sesuai BAB 2A.6 dokumen
            modul sistem.
        </p>
    </div>
@endsection