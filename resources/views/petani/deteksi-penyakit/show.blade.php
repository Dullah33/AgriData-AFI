@extends('layouts.dashboard')
@section('title', 'Hasil Deteksi Penyakit')
@section('content')

<div class="max-w-3xl">
    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('petani.deteksi-penyakit.index') }}"
       class="text-sm text-blue-600 hover:underline mb-4 inline-block">← Riwayat Deteksi</a>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <img src="{{ Storage::url($laporan->foto_tanaman) }}"
             class="w-full h-64 object-cover bg-gray-100">

        <div class="p-6">
            <div class="flex items-center justify-between flex-wrap gap-2 mb-4">
                <h2 class="text-xl font-bold text-gray-800">{{ $laporan->nama_penyakit }}</h2>
                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $laporan->badgeRisiko() }}">
                    Risiko {{ ucfirst($laporan->tingkat_risiko) }}
                </span>
            </div>

            <div class="flex items-center gap-2 mb-6">
                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 bg-green-500" style="width: {{ $laporan->confidence_score }}%"></div>
                </div>
                <span class="text-sm font-semibold text-gray-700">{{ $laporan->confidence_score }}% yakin</span>
            </div>

            <div class="grid md:grid-cols-1 gap-4 text-sm">
                <div>
                    <h3 class="font-semibold text-gray-700 mb-1">🩺 Gejala</h3>
                    <p class="text-gray-600">{{ $laporan->gejala }}</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700 mb-1">🔎 Penyebab</h3>
                    <p class="text-gray-600">{{ $laporan->penyebab }}</p>
                </div>
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <h3 class="font-semibold text-green-800 mb-1">✅ Cara Penanganan</h3>
                    <p class="text-green-700">{{ $laporan->penanganan }}</p>
                </div>
            </div>

            @if ($laporan->lahan)
                <p class="text-xs text-gray-400 mt-4">Terkait lahan: {{ $laporan->lahan->nama_lahan }}</p>
            @endif

            <hr class="my-5 border-gray-100">

            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs text-gray-500 mr-1">Status tindak lanjut Penyuluh:</span>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $laporan->badgeStatus() }}">
                        @if($laporan->status === 'baru') Menunggu Ditinjau
                        @elseif($laporan->status === 'ditinjau') Sedang Ditinjau
                        @else Sudah Ditindaklanjuti @endif
                    </span>
                </div>
                <a href="{{ route('petani.deteksi-penyakit.create') }}"
                   class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
                    🔬 Scan Foto Lain
                </a>
            </div>

            @if ($laporan->rekomendasi_tindak_lanjut)
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                    <h3 class="font-semibold text-blue-800 mb-1">👨‍🌾 Rekomendasi dari Penyuluh</h3>
                    <p class="text-blue-700">{{ $laporan->rekomendasi_tindak_lanjut }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
