@extends('layouts.dashboard')

@section('title', 'Dashboard Penyuluh')

@section('content')

@if (! $officer)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Selamat datang, {{ Auth::user()->username }} 👋</h2>
        <p class="text-gray-500 text-sm">
            Profil Penyuluh Anda belum terdaftar di sistem. Silakan hubungi Admin
            Dinas Pertanian untuk penetapan wilayah binaan.
        </p>
    </div>
@else
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Selamat datang, {{ Auth::user()->username }} 👋</h2>
        <p class="text-sm text-gray-500">Wilayah binaan: <span class="font-medium text-gray-700">{{ $officer->wilayah_binaan }}</span></p>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Petani Binaan</p>
            <p class="text-2xl font-bold text-gray-800">{{ $jumlahPetaniBinaan }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Kunjungan Bulan Ini</p>
            <p class="text-2xl font-bold text-gray-800">{{ $kunjunganBulanIni }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Jadwal Menunggu</p>
            <p class="text-2xl font-bold text-gray-800">{{ $kunjunganTerjadwal }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Pelatihan Bulan Ini</p>
            <p class="text-2xl font-bold text-gray-800">{{ $pelatihanBulanIni }}</p>
        </div>
    </div>

    {{-- Kunjungan terbaru --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-800">Kunjungan Terbaru</h3>
            <a href="{{ route('penyuluh.kunjungan.index') }}" class="text-xs text-teal-700 hover:underline">Lihat semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Petani</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($kunjunganTerbaru as $kunjungan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">{{ $kunjungan->tanggal_kunjungan->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-gray-800">{{ $kunjungan->petaniProfile->user->username ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $badge = match ($kunjungan->status) {
                                        'selesai' => 'bg-green-100 text-green-700',
                                        'batal' => 'bg-red-100 text-red-700',
                                        default => 'bg-yellow-100 text-yellow-700',
                                    };
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badge }}">{{ ucfirst($kunjungan->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">Belum ada kunjungan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
