@extends('layouts.dashboard')

@section('title', 'Laporan Kunjungan')

@section('content')

@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Laporan Hasil Kunjungan</h2>
        <a href="{{ route('penyuluh.kunjungan.index') }}" class="text-xs text-teal-700 hover:underline">
            Lihat Jadwal Kunjungan →
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Petani</th>
                    <th class="px-4 py-3">Rekomendasi</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($kunjungans as $kunjungan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-600">{{ $kunjungan->tanggal_kunjungan->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $kunjungan->petaniProfile->user->username ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600 max-w-sm truncate">{{ $kunjungan->rekomendasi }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('penyuluh.kunjungan.laporkan', $kunjungan) }}"
                               class="px-3 py-1 text-xs bg-teal-50 text-teal-700 hover:bg-teal-100 rounded-lg font-semibold">
                                Lihat / Ubah Laporan
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                            Belum ada laporan kunjungan yang selesai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($kunjungans->hasPages())
        <div class="mt-4">{{ $kunjungans->links() }}</div>
    @endif
</div>

@endsection
