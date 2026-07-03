@extends('layouts.dashboard')
@section('title', 'Dashboard Petani')
@section('content')

<div class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">Selamat datang, {{ Auth::user()->username }} 👋</h2>
        <p class="text-sm text-gray-500">Ringkasan aktivitas lahan dan penjualan Anda.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-xs text-gray-500 uppercase font-semibold">Produk Aktif</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['produk_aktif'] }}</p>
            <p class="text-xs text-gray-400 mt-1">listing tersedia</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-amber-500">
            <p class="text-xs text-gray-500 uppercase font-semibold">Pesanan Pending</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['pesanan_pending'] }}</p>
            <p class="text-xs text-gray-400 mt-1">menunggu konfirmasi</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total Lahan</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total_lahan'] }}</p>
            <p class="text-xs text-gray-400 mt-1">petak lahan</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-teal-500">
            <p class="text-xs text-gray-500 uppercase font-semibold">Kunjungan Terjadwal</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['kunjungan_terjadwal'] }}</p>
            <p class="text-xs text-gray-400 mt-1">dari penyuluh</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800 text-sm">Pesanan Masuk Terbaru</h3>
            <a href="{{ route('petani.pesanan.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
        </div>
        @forelse ($pesananTerbaru as $p)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0 text-sm">
                <div>
                    <p class="font-medium text-gray-800">{{ $p->produk->nama_produk }}</p>
                    <p class="text-xs text-gray-400">{{ $p->pembeli->username }} — {{ $p->jumlah }} {{ $p->produk->satuan }}</p>
                </div>
                <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">{{ $p->status_transaksi }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-400 py-2">Belum ada pesanan masuk.</p>
        @endforelse
    </div>
</div>
@endsection
