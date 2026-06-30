@extends('layouts.dashboard')

@section('title', 'Dashboard Petani')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Selamat datang, {{ Auth::user()->username }} 👋</h2>
        <p class="text-gray-500 text-sm">
            Ini halaman Dashboard Petani. Ringkasan status lahan, pesanan masuk, cuaca
            hari ini, dan notifikasi akan diisi sesuai BAB 2A.3 dokumen modul sistem.
        </p>
    </div>
@endsection