@extends('layouts.dashboard')

@section('title', 'Jadwal & Laporan Kunjungan')

@section('content')

@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Jadwal &amp; Laporan Kunjungan</h2>
        <a href="{{ route('penyuluh.kunjungan.create') }}"
           class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg">
            + Jadwalkan Kunjungan
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('penyuluh.kunjungan.index') }}" class="flex gap-3 mb-6">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            <option value="terjadwal" {{ request('status') === 'terjadwal' ? 'selected' : '' }}>Terjadwal</option>
            <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
            <option value="batal" {{ request('status') === 'batal' ? 'selected' : '' }}>Dibatalkan</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-sm rounded-lg">Filter</button>
        <a href="{{ route('penyuluh.kunjungan.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:underline self-center">Reset</a>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Petani</th>
                    <th class="px-4 py-3">Catatan Persiapan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($kunjungans as $kunjungan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-600">{{ $kunjungan->tanggal_kunjungan->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $kunjungan->petaniProfile->user->username ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $kunjungan->catatan_persiapan ?? '-' }}</td>
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
                        <td class="px-4 py-3">
                            @if ($kunjungan->status === 'terjadwal')
                                <div class="flex gap-2">
                                    <a href="{{ route('penyuluh.kunjungan.laporkan', $kunjungan) }}"
                                       class="px-3 py-1 text-xs bg-teal-50 text-teal-700 hover:bg-teal-100 rounded-lg font-semibold">
                                        Isi Laporan
                                    </a>
                                    <form method="POST" action="{{ route('penyuluh.kunjungan.batalkan', $kunjungan) }}"
                                          onsubmit="return confirm('Batalkan jadwal kunjungan ini?')">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1 text-xs bg-red-50 text-red-700 hover:bg-red-100 rounded-lg font-semibold">
                                            Batalkan
                                        </button>
                                    </form>
                                </div>
                            @elseif ($kunjungan->status === 'selesai')
                                <a href="{{ route('penyuluh.kunjungan.laporkan', $kunjungan) }}"
                                   class="px-3 py-1 text-xs bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-lg font-semibold">
                                    Lihat Laporan
                                </a>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                            Belum ada jadwal kunjungan.
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
