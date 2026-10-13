@extends('layouts.dashboard')
@section('title', 'Hasil Deteksi Penyakit')
@section('content')

<div class="max-w-3xl">
    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <img src="{{ Storage::url($laporan->foto_tanaman) }}" alt="Foto tanaman yang dianalisis"
             class="w-full h-64 object-cover bg-gray-100">

        <div class="p-6">
            <div class="flex items-center justify-between flex-wrap gap-2 mb-4">
                <h2 class="text-xl font-bold text-gray-800">{{ $laporan->nama_penyakit }}</h2>
                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $laporan->badgeRisiko() }}">
                    Risiko {{ ucfirst($laporan->tingkat_risiko) }}
                </span>
            </div>
            @if ($tanaman = $laporan->namaTanamanTerdeteksi())
                <p class="-mt-2 mb-4 text-sm text-gray-500">Tanaman terdeteksi: <span class="font-medium text-gray-700">{{ $tanaman }}</span></p>
            @endif

            <div class="flex items-center gap-2 mb-6">
                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 bg-green-500" style="width: {{ $laporan->confidence_score }}%"></div>
                </div>
                <span class="text-sm font-semibold text-gray-700">Tingkat akurasi: {{ $laporan->confidence_score }}%</span>
            </div>

            <div class="space-y-4 text-sm">
                <div>
                    <h3 class="font-semibold text-gray-700 mb-1">🩺 Gejala</h3>
                    <p class="text-gray-600 whitespace-pre-line">{{ $laporan->gejala }}</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700 mb-1">🔎 Penyebab</h3>
                    <p class="text-gray-600 whitespace-pre-line">{{ $laporan->penyebab }}</p>
                </div>
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <h3 class="font-semibold text-green-800 mb-1">✅ Cara Penanganan (Umum)</h3>
                    <p class="text-green-700 whitespace-pre-line">{{ $laporan->penanganan }}</p>
                </div>
            </div>

            <p class="text-xs text-amber-600 mt-5">
                ℹ️ Hasil ini bersifat edukatif. Untuk penanganan di lahan pertanian sungguhan, sebaiknya
                konsultasikan juga dengan Petugas Penyuluh Pertanian setempat.
            </p>

            <a href="{{ route('user.deteksi-penyakit.create') }}"
               class="inline-block mt-4 px-4 py-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold rounded-lg">
                🔬 Scan Foto Lain
            </a>
        </div>
    </div>
</div>
@endsection
