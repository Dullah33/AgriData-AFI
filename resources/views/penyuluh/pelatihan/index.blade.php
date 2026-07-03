@extends('layouts.dashboard')

@section('title', 'Pelatihan Kelompok Tani')

@section('content')

@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Pelatihan Kelompok Tani</h2>
        <a href="{{ route('penyuluh.pelatihan.create') }}"
           class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg">
            + Jadwalkan Pelatihan
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Lokasi</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Peserta</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($pelatihans as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $p->judul }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $p->lokasi }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $p->tanggal_pelaksanaan->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $p->jumlah_peserta ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badge = match ($p->status) {
                                    'selesai' => 'bg-green-100 text-green-700',
                                    'batal' => 'bg-red-100 text-red-700',
                                    default => 'bg-yellow-100 text-yellow-700',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badge }}">{{ ucfirst($p->status) }}</span>
                        </td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('penyuluh.pelatihan.edit', $p) }}"
                               class="px-3 py-1 text-xs bg-teal-50 text-teal-700 hover:bg-teal-100 rounded-lg font-semibold">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('penyuluh.pelatihan.destroy', $p) }}"
                                  onsubmit="return confirm('Hapus kegiatan pelatihan ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1 text-xs bg-red-50 text-red-700 hover:bg-red-100 rounded-lg font-semibold">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                            Belum ada kegiatan pelatihan terjadwal.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($pelatihans->hasPages())
        <div class="mt-4">{{ $pelatihans->links() }}</div>
    @endif
</div>

@endsection
