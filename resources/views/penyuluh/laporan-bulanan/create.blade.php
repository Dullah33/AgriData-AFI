@extends('layouts.dashboard')
@section('title', 'Buat Laporan Bulanan')
@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('penyuluh.laporan-bulanan.index') }}" class="text-sm text-teal-700 hover:underline">← Kembali</a>
        <h2 class="text-lg font-semibold text-gray-800">Buat Laporan Kinerja Bulanan</h2>
    </div>

    {{-- Ringkasan otomatis sebagai bantuan pengisian --}}
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-teal-50 rounded-lg p-4">
            <p class="text-xs text-teal-700 uppercase font-semibold">Kunjungan Bulan Ini</p>
            <p class="text-2xl font-bold text-teal-800">{{ $jumlahKunjunganBulanIni }}</p>
        </div>
        <div class="bg-teal-50 rounded-lg p-4">
            <p class="text-xs text-teal-700 uppercase font-semibold">Pelatihan Bulan Ini</p>
            <p class="text-2xl font-bold text-teal-800">{{ $jumlahPelatihanBulanIni }}</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('penyuluh.laporan-bulanan.store') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan <span class="text-red-500">*</span></label>
                <select name="bulan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                    @php
                        $namaBulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                    @endphp
                    @foreach ($namaBulan as $val => $label)
                        <option value="{{ $val }}" {{ old('bulan', now()->month) == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                <input type="number" name="tahun" value="{{ old('tahun', now()->year) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ringkasan Kegiatan <span class="text-red-500">*</span></label>
            <textarea name="ringkasan_kegiatan" rows="4"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                      placeholder="Ringkasan kunjungan, pelatihan, dan kegiatan pendampingan lain bulan ini...">{{ old('ringkasan_kegiatan') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kendala</label>
            <textarea name="kendala" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                      placeholder="Kendala yang dihadapi selama bulan berjalan (opsional)">{{ old('kendala') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rencana Tindak Lanjut</label>
            <textarea name="rencana_tindak_lanjut" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                      placeholder="Rencana untuk bulan berikutnya (opsional)">{{ old('rencana_tindak_lanjut') }}</textarea>
        </div>

        <p class="text-xs text-gray-400">Laporan akan tersimpan sebagai draft dulu — bisa diedit sebelum dikirim ke Admin.</p>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-6 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg">
                💾 Simpan sebagai Draft
            </button>
            <a href="{{ route('penyuluh.laporan-bulanan.index') }}"
               class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
