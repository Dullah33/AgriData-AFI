@extends('layouts.dashboard')
@section('title', 'Jadwalkan Kunjungan')
@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('penyuluh.kunjungan.index') }}" class="text-sm text-teal-700 hover:underline">← Kembali</a>
        <h2 class="text-lg font-semibold text-gray-800">Jadwalkan Kunjungan Lapangan</h2>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('penyuluh.kunjungan.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Petani yang Dikunjungi <span class="text-red-500">*</span></label>
            <select name="petani_profile_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                <option value="">-- Pilih Petani --</option>
                @foreach ($petaniBinaan as $petani)
                    <option value="{{ $petani->id }}" {{ old('petani_profile_id') == $petani->id ? 'selected' : '' }}>
                        {{ $petani->user->username ?? '-' }} — {{ $petani->nama_kelompok_tani ?? 'Tanpa kelompok tani' }}
                    </option>
                @endforeach
            </select>
            @if ($petaniBinaan->isEmpty())
                <p class="text-xs text-amber-600 mt-1">Belum ada petani terdaftar di wilayah binaan Anda.</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kunjungan <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_kunjungan" value="{{ old('tanggal_kunjungan') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Persiapan</label>
            <textarea name="catatan_persiapan" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                      placeholder="Hal-hal yang perlu disiapkan/diperiksa saat kunjungan (opsional)">{{ old('catatan_persiapan') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-6 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg">
                📅 Simpan Jadwal
            </button>
            <a href="{{ route('penyuluh.kunjungan.index') }}"
               class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
