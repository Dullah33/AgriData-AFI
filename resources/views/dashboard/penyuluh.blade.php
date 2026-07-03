@extends('layouts.dashboard')
@section('title', 'Dashboard Penyuluh')
@section('content')

<div class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">Selamat datang, {{ Auth::user()->username }} 👋</h2>
        <p class="text-sm text-gray-500">Wilayah binaan: <strong class="text-teal-700">{{ $officer?->wilayah_binaan ?? 'Belum ditetapkan' }}</strong></p>
    </div>

    @if (!$officer)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-sm text-yellow-800">
            ⚠️ Akun Penyuluh Anda belum ditetapkan wilayah binaan oleh Admin. Hubungi Admin untuk penetapan wilayah.
        </div>
    @else
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-teal-500">
                <p class="text-xs text-gray-500 uppercase font-semibold">Petani Binaan</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['petani_binaan'] }}</p>
                <p class="text-xs text-gray-400 mt-1">di wilayah Anda</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
                <p class="text-xs text-gray-500 uppercase font-semibold">Kunjungan Bulan Ini</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['kunjungan_bulan_ini'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['kunjungan_terjadwal'] }} terjadwal</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
                <p class="text-xs text-gray-500 uppercase font-semibold">Pelatihan Bulan Ini</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['pelatihan_bulan_ini'] }}</p>
                <p class="text-xs text-gray-400 mt-1">kegiatan</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 text-sm">Jadwal Kunjungan Mendatang</h3>
                <a href="{{ route('penyuluh.kunjungan.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
            </div>
            @forelse ($kunjunganMendatang as $k)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0 text-sm">
                    <div>
                        <p class="font-medium text-gray-800">{{ $k->petaniProfile->user->username ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $k->tanggal_kunjungan->format('d M Y') }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">Terjadwal</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-2">Tidak ada jadwal mendatang.</p>
            @endforelse
        </div>
    @endif
</div>
@endsection
