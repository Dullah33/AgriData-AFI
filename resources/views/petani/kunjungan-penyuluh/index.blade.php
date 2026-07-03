@extends('layouts.dashboard')
@section('title', 'Kunjungan Penyuluh')
@section('content')

<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-6">Riwayat & Jadwal Kunjungan Penyuluh</h2>

    @if ($kunjungans->isEmpty())
        <div class="text-center py-10 text-gray-400">
            <p class="text-4xl mb-3">📋</p>
            <p class="text-sm">Belum ada jadwal kunjungan dari petugas penyuluh di wilayah Anda.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Petugas Penyuluh</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Rekomendasi</th>
                        <th class="px-4 py-3">Kondisi Lahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($kunjungans as $k)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $k->extensionOfficer->user->username ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $k->tanggal_kunjungan->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $badge = ['terjadwal' => 'blue', 'selesai' => 'green', 'batal' => 'red'][$k->status] ?? 'gray';
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold bg-{{ $badge }}-100 text-{{ $badge }}-700 rounded-full capitalize">
                                    {{ $k->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 max-w-xs">
                                @if ($k->rekomendasi)
                                    <p class="text-sm leading-relaxed">{{ $k->rekomendasi }}</p>
                                @else
                                    <span class="text-gray-400 text-xs italic">Belum ada rekomendasi</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 max-w-xs">
                                @if ($k->kondisi_lahan)
                                    <p class="text-sm leading-relaxed">{{ $k->kondisi_lahan }}</p>
                                @else
                                    <span class="text-gray-400 text-xs italic">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($kunjungans->hasPages())
            <div class="mt-4">{{ $kunjungans->links() }}</div>
        @endif
    @endif
</div>
@endsection
