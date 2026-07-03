@extends('layouts.dashboard')
@section('title', 'Detail Laporan Deteksi Penyakit')
@section('content')

<div class="max-w-3xl">
    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('penyuluh.deteksi-penyakit.index') }}"
       class="text-sm text-teal-700 hover:underline mb-4 inline-block">← Deteksi Penyakit (Wilayah)</a>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <img src="{{ Storage::url($laporan->foto_tanaman) }}"
             class="w-full h-64 object-cover bg-gray-100">

        <div class="p-6">
            <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                <h2 class="text-xl font-bold text-gray-800">{{ $laporan->nama_penyakit }}</h2>
                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $laporan->badgeRisiko() }}">
                    Risiko {{ ucfirst($laporan->tingkat_risiko) }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mb-1">
                Dilaporkan oleh <strong>{{ $laporan->user->username ?? '-' }}</strong>
                pada {{ $laporan->created_at->translatedFormat('d M Y, H:i') }}
            </p>
            @if ($laporan->lahan)
                <p class="text-sm text-gray-500 mb-4">Lahan: {{ $laporan->lahan->nama_lahan }} ({{ $laporan->lahan->tanaman_aktif ?? '-' }})</p>
            @endif

            <div class="flex items-center gap-2 mb-6">
                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 bg-teal-500" style="width: {{ $laporan->confidence_score }}%"></div>
                </div>
                <span class="text-sm font-semibold text-gray-700">{{ $laporan->confidence_score }}% yakin (AI)</span>
            </div>

            <div class="space-y-4 text-sm">
                <div>
                    <h3 class="font-semibold text-gray-700 mb-1">🩺 Gejala</h3>
                    <p class="text-gray-600">{{ $laporan->gejala }}</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700 mb-1">🔎 Penyebab</h3>
                    <p class="text-gray-600">{{ $laporan->penyebab }}</p>
                </div>
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <h3 class="font-semibold text-gray-700 mb-1">Penanganan Umum (hasil AI)</h3>
                    <p class="text-gray-600">{{ $laporan->penanganan }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Rekomendasi Tindak Lanjut --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-3">👨‍🌾 Rekomendasi Tindak Lanjut Penyuluh</h3>

        @if ($laporan->status === 'ditindaklanjuti')
            <div class="p-4 bg-teal-50 border border-teal-200 rounded-lg text-sm text-teal-800">
                <p class="mb-1 font-semibold">Sudah ditindaklanjuti pada {{ $laporan->ditinjau_at?->translatedFormat('d M Y, H:i') }}:</p>
                <p>{{ $laporan->rekomendasi_tindak_lanjut }}</p>
            </div>
        @else
            @if ($errors->any())
                <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('penyuluh.deteksi-penyakit.tindak-lanjut', $laporan) }}">
                @csrf
                <textarea name="rekomendasi_tindak_lanjut" rows="4" required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                          placeholder="Contoh: Segera pangkas bagian daun yang terinfeksi dan pastikan drainase lahan lancar. Saya akan kunjungi lahan minggu depan.">{{ old('rekomendasi_tindak_lanjut') }}</textarea>
                <button type="submit"
                        class="mt-3 px-6 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg border border-teal-700">
                    Kirim Rekomendasi
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
