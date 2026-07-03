@extends('layouts.dashboard')
@section('title', 'Edit Laporan Bulanan')
@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('penyuluh.laporan-bulanan.index') }}" class="text-sm text-teal-700 hover:underline">← Kembali</a>
        <h2 class="text-lg font-semibold text-gray-800">Edit Laporan: {{ $laporan->periode_label }}</h2>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('penyuluh.laporan-bulanan.update', $laporan) }}" class="space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ringkasan Kegiatan <span class="text-red-500">*</span></label>
            <textarea name="ringkasan_kegiatan" rows="4"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('ringkasan_kegiatan', $laporan->ringkasan_kegiatan) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kendala</label>
            <textarea name="kendala" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('kendala', $laporan->kendala) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rencana Tindak Lanjut</label>
            <textarea name="rencana_tindak_lanjut" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('rencana_tindak_lanjut', $laporan->rencana_tindak_lanjut) }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-6 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg">
                💾 Simpan Perubahan
            </button>
            <a href="{{ route('penyuluh.laporan-bulanan.index') }}"
               class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
