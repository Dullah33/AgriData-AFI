@extends('layouts.dashboard')
@section('title', 'Jadwalkan Pelatihan')
@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('penyuluh.pelatihan.index') }}" class="text-sm text-teal-700 hover:underline">← Kembali</a>
        <h2 class="text-lg font-semibold text-gray-800">Jadwalkan Pelatihan Kelompok Tani</h2>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('penyuluh.pelatihan.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Kegiatan <span class="text-red-500">*</span></label>
            <input type="text" name="judul" value="{{ old('judul') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                   placeholder="Contoh: Pelatihan Pemupukan Berimbang">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi / Materi</label>
            <textarea name="deskripsi" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                      placeholder="Ringkasan materi yang akan disampaikan (opsional)">{{ old('deskripsi') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
                <input type="text" name="lokasi" value="{{ old('lokasi') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                       placeholder="Balai Desa, dsb.">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pelaksanaan <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_pelaksanaan" value="{{ old('tanggal_pelaksanaan') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Perkiraan Jumlah Peserta</label>
            <input type="number" min="0" name="jumlah_peserta" value="{{ old('jumlah_peserta') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                   placeholder="Opsional">
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-6 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg">
                📅 Simpan Jadwal
            </button>
            <a href="{{ route('penyuluh.pelatihan.index') }}"
               class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
