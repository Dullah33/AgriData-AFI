@extends('layouts.dashboard')

@section('title', 'Detail Petugas Penyuluh')

@section('content')

@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.penyuluh.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">&larr; Kembali</a>
    <h2 class="text-lg font-semibold text-gray-800">Detail Penyuluh: {{ $penyuluh->user->username ?? '-' }}</h2>
</div>

{{-- Ringkasan profil --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-semibold text-gray-800">Profil</h3>
        @if ($penyuluh->status === 'aktif')
            <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Aktif</span>
        @else
            <span class="px-2 py-1 text-xs font-semibold bg-gray-200 text-gray-600 rounded-full">Nonaktif</span>
        @endif
    </div>
    <dl class="grid grid-cols-2 gap-y-3 text-sm max-w-xl">
        <dt class="text-gray-500">Email</dt>
        <dd class="text-gray-800">{{ $penyuluh->user->email ?? '-' }}</dd>

        <dt class="text-gray-500">NIP</dt>
        <dd class="text-gray-800">{{ $penyuluh->nip ?? '-' }}</dd>

        <dt class="text-gray-500">Wilayah Binaan</dt>
        <dd class="text-gray-800">{{ $penyuluh->wilayah_binaan }}</dd>

        <dt class="text-gray-500">Telepon</dt>
        <dd class="text-gray-800">{{ $penyuluh->phone ?? '-' }}</dd>

        <dt class="text-gray-500">Ditugaskan Oleh</dt>
        <dd class="text-gray-800">{{ $penyuluh->assignedBy->username ?? '-' }}</dd>

        <dt class="text-gray-500">Jumlah Petani Binaan</dt>
        <dd class="text-gray-800">{{ $jumlahPetaniBinaan }} petani</dd>
    </dl>

    <div class="mt-5">
        <a href="{{ route('admin.penyuluh.edit', $penyuluh) }}"
           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">
            Edit Data
        </a>
    </div>
</div>

{{-- Kunjungan terbaru --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-base font-semibold text-gray-800 mb-4">Kunjungan Lapangan Terbaru</h3>
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
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">Belum ada riwayat kunjungan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Laporan bulanan --}}
<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-base font-semibold text-gray-800 mb-4">Laporan Kinerja Bulanan</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Dikirim</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($laporanBulanan as $laporan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-800">{{ $laporan->periode_label }}</td>
                        <td class="px-4 py-3">
                            @if ($laporan->status === 'terkirim')
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Terkirim</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-gray-200 text-gray-600 rounded-full">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $laporan->submitted_at?->format('d M Y') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">Belum ada laporan bulanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
