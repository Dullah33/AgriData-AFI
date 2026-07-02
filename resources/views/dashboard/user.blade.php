@extends('layouts.dashboard')

@section('title', 'Dashboard User')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Selamat datang, {{ Auth::user()->username }} 👋</h2>
        <p class="text-gray-500 text-sm">
            Ini halaman Dashboard User. Ringkasan pesanan aktif, rekomendasi produk,
            dan artikel terbaru akan diisi sesuai BAB 2A.4 dokumen modul sistem.
        </p>
    </div>
@endsection