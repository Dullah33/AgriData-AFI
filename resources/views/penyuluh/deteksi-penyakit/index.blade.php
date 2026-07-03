@extends('layouts.dashboard')
@section('title', 'Deteksi Penyakit di Wilayah Binaan')
@section('content')

@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

{{-- Stat cards --}}
<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-500 mb-1">Total Laporan</p>
        <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-500 mb-1">Menunggu Ditinjau</p>
        <p class="text-2xl font-bold text-amber-600">{{ $stats['baru'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-500 mb-1">Sudah Ditindaklanjuti</p>
        <p class="text-2xl font-bold text-teal-600">{{ $stats['ditindaklanjuti'] }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Deteksi Penyakit — Wilayah {{ $officer->wilayah_binaan }}</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Foto</th>
                    <th class="px-4 py-3">Penyakit</th>
                    <th class="px-4 py-3">Pelapor</th>
                    <th class="px-4 py-3">Risiko</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($laporan as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <img src="{{ Storage::url($item->foto_tanaman) }}"
                                 class="w-10 h-10 rounded-lg object-cover border border-gray-200">
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $item->nama_penyakit }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $item->user->username ?? '-' }}
                            @if ($item->lahan)
                                <span class="block text-xs text-gray-400">Lahan: {{ $item->lahan->nama_lahan }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $item->badgeRisiko() }}">
                                {{ ucfirst($item->tingkat_risiko) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $item->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $item->badgeStatus() }}">
                                @if($item->status === 'baru') Baru
                                @elseif($item->status === 'ditinjau') Ditinjau
                                @else Ditindaklanjuti @endif
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('penyuluh.deteksi-penyakit.show', $item) }}"
                               class="inline-block px-3 py-1.5 text-xs font-semibold text-teal-700 bg-teal-100 border border-teal-300 rounded-lg hover:bg-teal-200">
                                Tinjau
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            Belum ada laporan deteksi penyakit dari petani di wilayah binaan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($laporan->hasPages())
        <div class="mt-4">{{ $laporan->links() }}</div>
    @endif
</div>
@endsection
