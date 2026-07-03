@extends('layouts.dashboard')
@section('title', 'Dashboard Admin')
@section('content')

<div class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">Selamat datang, {{ Auth::user()->username }} 👋</h2>
        <p class="text-sm text-gray-500">Ringkasan platform Agri Data per hari ini.</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total Petani</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total_petani'] }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['petani_terverifikasi'] }} terverifikasi</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-xs text-gray-500 uppercase font-semibold">Transaksi Bulan Ini</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['transaksi_bulan_ini'] }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['transaksi_pending'] }} pending</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-teal-500">
            <p class="text-xs text-gray-500 uppercase font-semibold">Penyuluh Aktif</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['penyuluh_aktif'] }}</p>
            <p class="text-xs text-gray-400 mt-1">petugas lapangan</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-amber-500">
            <p class="text-xs text-gray-500 uppercase font-semibold">Artikel Published</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['artikel_published'] }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['artikel_draft'] }} draft</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Petani Belum Verifikasi --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 text-sm">Petani Menunggu Verifikasi</h3>
                <a href="{{ route('admin.petani.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
            </div>
            @forelse ($petaniBelumVerifikasi as $p)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0 text-sm">
                    <div>
                        <p class="font-medium text-gray-800">{{ $p->user->username }}</p>
                        <p class="text-xs text-gray-400">{{ $p->komoditas_utama ?? 'Belum diisi' }}</p>
                    </div>
                    <a href="{{ route('admin.petani.show', $p) }}"
                       class="px-3 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200">
                        Verifikasi
                    </a>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-2">Semua petani sudah terverifikasi ✓</p>
            @endforelse
        </div>

        {{-- Ulasan Terbaru --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 text-sm">Ulasan Terbaru</h3>
                <a href="{{ route('admin.ulasan.index') }}" class="text-xs text-blue-600 hover:underline">Moderasi →</a>
            </div>
            @forelse ($ulasanTerbaru as $u)
                <div class="py-2 border-b border-gray-100 last:border-0 text-sm">
                    <div class="flex items-center gap-1 mb-1">
                        <span class="text-amber-400 font-bold">{{ str_repeat('★', $u->rating) }}{{ str_repeat('☆', 5 - $u->rating) }}</span>
                    </div>
                    <p class="text-gray-600 text-xs truncate">{{ $u->komentar ?? 'Tanpa komentar' }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-2">Belum ada ulasan.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
