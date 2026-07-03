@extends('layouts.dashboard')

@section('title', 'Kunjungan Penyuluh')

@section('content')

<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-6">Riwayat Kunjungan Penyuluh</h2>

    <div class="space-y-4">
        @forelse ($kunjungans as $kunjungan)
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <p class="font-medium text-gray-800">{{ $kunjungan->tanggal_kunjungan->format('d M Y') }}</p>
                        <p class="text-xs text-gray-500">
                            Penyuluh: {{ $kunjungan->extensionOfficer->user->username ?? '-' }}
                        </p>
                    </div>
                    @php
                        $badge = match ($kunjungan->status) {
                            'selesai' => 'bg-green-100 text-green-700',
                            'batal' => 'bg-red-100 text-red-700',
                            default => 'bg-yellow-100 text-yellow-700',
                        };
                    @endphp
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badge }}">{{ ucfirst($kunjungan->status) }}</span>
                </div>

                @if ($kunjungan->status === 'selesai')
                    <dl class="text-sm mt-3 space-y-2">
                        <div>
                            <dt class="text-gray-500 text-xs uppercase font-semibold">Kondisi Lahan</dt>
                            <dd class="text-gray-700">{{ $kunjungan->kondisi_lahan }}</dd>
                        </div>
                        @if ($kunjungan->kendala_ditemukan)
                            <div>
                                <dt class="text-gray-500 text-xs uppercase font-semibold">Kendala</dt>
                                <dd class="text-gray-700">{{ $kunjungan->kendala_ditemukan }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-gray-500 text-xs uppercase font-semibold">Rekomendasi</dt>
                            <dd class="text-gray-700">{{ $kunjungan->rekomendasi }}</dd>
                        </div>
                    </dl>
                @elseif ($kunjungan->catatan_persiapan)
                    <p class="text-sm text-gray-500 mt-2">Catatan persiapan: {{ $kunjungan->catatan_persiapan }}</p>
                @endif
            </div>
        @empty
            <p class="text-center text-gray-400 py-8">Belum ada riwayat kunjungan dari penyuluh.</p>
        @endforelse
    </div>

    @if ($kunjungans->hasPages())
        <div class="mt-4">{{ $kunjungans->links() }}</div>
    @endif
</div>

@endsection
