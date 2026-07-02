@extends('layouts.dashboard')
@section('title', 'Edit Lahan')
@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('petani.lahan.index') }}"
           class="text-sm text-blue-600 hover:underline">← Kembali</a>
        <h2 class="text-lg font-semibold text-gray-800">Edit Lahan: {{ $lahan->nama_lahan }}</h2>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('petani.lahan.update', $lahan) }}" class="space-y-4">
        @csrf @method('PUT')

        {{-- Nama Lahan --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama/Kode Lahan <span class="text-red-500">*</span></label>
            <input type="text" name="nama_lahan" value="{{ old('nama_lahan', $lahan->nama_lahan) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Luas & Jenis Tanah --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Luas Lahan (Ha) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" min="0.01" name="luas_ha" value="{{ old('luas_ha', $lahan->luas_ha) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Tanah</label>
                <select name="jenis_tanah"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Jenis Tanah --</option>
                    @foreach ($jenisTanahList as $j)
                        <option value="{{ $j }}" {{ old('jenis_tanah', $lahan->jenis_tanah) === $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Tanaman Aktif --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanaman yang Sedang Ditanam</label>
            <input type="text" name="tanaman_aktif" value="{{ old('tanaman_aktif', $lahan->tanaman_aktif) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Kosongkan jika lahan sedang bera">
        </div>

        {{-- Tanggal Tanam & Perkiraan Panen --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Tanam</label>
                <input type="date" name="tanggal_tanam"
                       value="{{ old('tanggal_tanam', $lahan->tanggal_tanam?->format('Y-m-d')) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Perkiraan Tanggal Panen</label>
                <input type="date" name="perkiraan_panen"
                       value="{{ old('perkiraan_panen', $lahan->perkiraan_panen?->format('Y-m-d')) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        {{-- Status --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status Lahan <span class="text-red-500">*</span></label>
            <select name="status"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach (['aktif' => 'Aktif Ditanami', 'bera' => 'Bera (Tidak Ditanami)', 'panen' => 'Siap/Sudah Panen'] as $val => $label)
                    <option value="{{ $val }}" {{ old('status', $lahan->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg border border-blue-700">
                💾 Simpan Perubahan
            </button>
            <a href="{{ route('petani.lahan.index') }}"
               class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
