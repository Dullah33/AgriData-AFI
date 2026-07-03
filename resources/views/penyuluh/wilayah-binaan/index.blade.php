@extends('layouts.dashboard')

@section('title', 'Wilayah Binaan')

@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-1">Wilayah Binaan: {{ $officer->wilayah_binaan }}</h2>
    <p class="text-sm text-gray-500">{{ $petaniBinaan->count() }} petani terdaftar di wilayah ini.</p>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-base font-semibold text-gray-800 mb-4">Daftar Petani Binaan</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Nama / Username</th>
                    <th class="px-4 py-3">Kelompok Tani</th>
                    <th class="px-4 py-3">Komoditas Utama</th>
                    <th class="px-4 py-3">Status Verifikasi</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($petaniBinaan as $petani)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $petani->user->username ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $petani->nama_kelompok_tani ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $petani->komoditas_utama ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($petani->isTerverifikasi())
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Terverifikasi</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">Belum Diverifikasi</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('penyuluh.kunjungan.create') }}"
                               class="px-3 py-1 text-xs bg-teal-50 text-teal-700 hover:bg-teal-100 rounded-lg font-semibold">
                                Jadwalkan Kunjungan
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                            Belum ada petani terdaftar di wilayah ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
