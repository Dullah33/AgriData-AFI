@extends('layouts.dashboard')
@section('title', 'AI Scanner Penyakit Tanaman')
@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route('petani.deteksi-penyakit.index') }}"
           class="text-sm text-blue-600 hover:underline">← Riwayat Deteksi</a>
    </div>
    <h2 class="text-lg font-semibold text-gray-800 mb-1">🔬 AI Scanner Penyakit Tanaman</h2>
    <p class="text-sm text-gray-500 mb-6">
        Upload foto tanaman yang terindikasi sakit. Sistem akan menganalisis foto dan
        menampilkan nama penyakit, gejala, penyebab, serta cara penanganannya.
    </p>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('petani.deteksi-penyakit.store') }}"
          enctype="multipart/form-data" class="space-y-4" id="scan-form">
        @csrf

        {{-- Pilih Lahan (opsional) --}}
        @if ($lahans->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lahan Terkait (opsional)</label>
                <select name="lahan_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Tidak terkait lahan tertentu --</option>
                    @foreach ($lahans as $l)
                        <option value="{{ $l->id }}" {{ old('lahan_id') == $l->id ? 'selected' : '' }}>
                            {{ $l->nama_lahan }} @if($l->tanaman_aktif) ({{ $l->tanaman_aktif }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Upload Foto --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Foto Tanaman <span class="text-red-500">*</span></label>
            <label for="foto_tanaman"
                   class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 hover:border-green-400 transition overflow-hidden">
                <div id="preview-placeholder" class="flex flex-col items-center justify-center text-center px-4">
                    <svg class="w-9 h-9 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-gray-500 font-medium">Klik untuk upload foto tanaman</p>
                    <p class="text-xs text-gray-400 mt-1">JPG atau PNG — Maks. 5MB</p>
                </div>
                <img id="preview-image" class="hidden max-h-full max-w-full object-contain" />
                <input id="foto_tanaman" type="file" name="foto_tanaman" accept="image/jpeg,image/png" class="hidden" required>
            </label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" id="submit-btn"
                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg border border-green-700">
                🔍 Analisis Sekarang
            </button>
            <a href="{{ route('dashboard') }}"
               class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
    const input = document.getElementById('foto_tanaman');
    const placeholder = document.getElementById('preview-placeholder');
    const previewImg = document.getElementById('preview-image');

    input.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    document.getElementById('scan-form').addEventListener('submit', function () {
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.textContent = '⏳ Menganalisis foto...';
    });
</script>
@endsection
