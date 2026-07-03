@extends('layouts.dashboard')
@section('title', 'Laporan Kunjungan')
@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('penyuluh.kunjungan.index') }}" class="text-sm text-teal-700 hover:underline">← Kembali</a>
        <h2 class="text-lg font-semibold text-gray-800">
            Laporan Kunjungan: {{ $kunjungan->petaniProfile->user->username ?? '-' }}
        </h2>
    </div>

    <p class="text-xs text-gray-400 mb-4">
        Kunjungan tanggal {{ $kunjungan->tanggal_kunjungan->format('d M Y') }}
        @if ($kunjungan->catatan_persiapan)
            &middot; Catatan persiapan: {{ $kunjungan->catatan_persiapan }}
        @endif
    </p>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('penyuluh.kunjungan.simpan-laporan', $kunjungan) }}"
          enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi Lahan <span class="text-red-500">*</span></label>
            <textarea name="kondisi_lahan" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                      placeholder="Kondisi lahan saat kunjungan...">{{ old('kondisi_lahan', $kunjungan->kondisi_lahan) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kendala yang Ditemukan</label>
            <textarea name="kendala_ditemukan" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                      placeholder="Kendala di lapangan (opsional)">{{ old('kendala_ditemukan', $kunjungan->kendala_ditemukan) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rekomendasi <span class="text-red-500">*</span></label>
            <textarea name="rekomendasi" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                      placeholder="Rekomendasi yang diberikan kepada petani...">{{ old('rekomendasi', $kunjungan->rekomendasi) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Foto Dokumentasi</label>
            @if ($kunjungan->foto_dokumentasi)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($kunjungan->foto_dokumentasi) }}"
                     alt="Dokumentasi kunjungan" class="w-40 rounded-lg border border-gray-200 mb-2">
            @endif
            <input type="file" name="foto_dokumentasi" accept="image/*"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <p class="text-xs text-gray-400 mt-1">Format JPG/PNG, maks. 5MB. Kosongkan jika tidak ingin mengubah foto.</p>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-6 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg">
                💾 Simpan Laporan
            </button>
            <a href="{{ route('penyuluh.kunjungan.index') }}"
               class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
