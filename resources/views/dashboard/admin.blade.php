@extends('layouts.dashboard')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Selamat datang, {{ Auth::user()->username }} 👋</h2>
        <p class="text-gray-500 text-sm">
            Ini halaman Dashboard Admin. Kerangka routing & sidebar sudah jalan —
            kartu statistik (total petani, transaksi, laporan penyakit, penyuluh aktif)
            menyusul di iterasi berikutnya sesuai BAB 2A.2 dokumen modul sistem.
        </p>
    </div>
@endsection