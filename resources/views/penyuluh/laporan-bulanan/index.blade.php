@extends('layouts.dashboard')

@section('title', 'Laporan Kinerja Bulanan')

@section('content')

@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Laporan Kinerja Bulanan</h2>
        <a href="{{ route('penyuluh.laporan-bulanan.create') }}"
           class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg">
            + Buat Laporan
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Ringkasan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Dikirim</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($laporans as $laporan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $laporan->periode_label }}</td>
                        <td class="px-4 py-3 text-gray-600 max-w-sm truncate">{{ $laporan->ringkasan_kegiatan }}</td>
                        <td class="px-4 py-3">
                            @if ($laporan->status === 'terkirim')
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Terkirim</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-gray-200 text-gray-600 rounded-full">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $laporan->submitted_at?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($laporan->status === 'draft')
                                <div class="flex gap-2">
                                    <a href="{{ route('penyuluh.laporan-bulanan.edit', $laporan) }}"
                                       class="px-3 py-1 text-xs bg-teal-50 text-teal-700 hover:bg-teal-100 rounded-lg font-semibold">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('penyuluh.laporan-bulanan.kirim', $laporan) }}"
                                          onsubmit="return confirm('Kirim laporan ini ke Admin? Laporan tidak bisa diedit lagi setelah dikirim.')">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1 text-xs bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg font-semibold">
                                            Kirim
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                            Belum ada laporan bulanan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($laporans->hasPages())
        <div class="mt-4">{{ $laporans->links() }}</div>
    @endif
</div>

@endsection
