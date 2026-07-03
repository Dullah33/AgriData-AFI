@extends('layouts.dashboard')
@section('title', 'Riwayat Deteksi Penyakit')
@section('content')

@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-gray-800">Riwayat Deteksi Penyakit</h2>
    <a href="{{ route('petani.deteksi-penyakit.create') }}"
       class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
        🔬 Scan Foto Baru
    </a>
</div>

@if ($laporan->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
        Belum ada riwayat deteksi penyakit. Klik "Scan Foto Baru" untuk mulai memakai AI Scanner.
    </div>
@else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($laporan as $item)
            <a href="{{ route('petani.deteksi-penyakit.show', $item) }}"
               class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition block">
                <img src="{{ Storage::url($item->foto_tanaman) }}" class="w-full h-36 object-cover bg-gray-100">
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <h3 class="font-semibold text-gray-800 text-sm">{{ $item->nama_penyakit }}</h3>
                        <span class="shrink-0 px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $item->badgeRisiko() }}">
                            {{ ucfirst($item->tingkat_risiko) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</p>
                    <div class="mt-2">
                        <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $item->badgeStatus() }}">
                            @if($item->status === 'baru') Menunggu Ditinjau
                            @elseif($item->status === 'ditinjau') Sedang Ditinjau
                            @else Ditindaklanjuti @endif
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    @if ($laporan->hasPages())
        <div class="mt-6">{{ $laporan->links() }}</div>
    @endif
@endif
@endsection
