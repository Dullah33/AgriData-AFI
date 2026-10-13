@extends('layouts.dashboard')
@section('title', 'AI Scanner Penyakit Tanaman')
@section('content')

<div class="max-w-5xl">
    <a href="{{ route('petani.deteksi-penyakit.index') }}"
       class="inline-block mb-4 text-sm text-blue-600 hover:underline">← Riwayat Deteksi</a>

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm p-5 sm:p-6">
            <div class="flex items-start gap-3 mb-6">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-green-100 text-green-700 flex items-center justify-center">
                    <i data-lucide="scan-search" class="w-6 h-6"></i>
                </span>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Scan Penyakit Tanaman</h2>
                    <p class="text-sm text-gray-500">
                        Foto tanaman yang terlihat sakit langsung dari kamera, atau unggah dari galeri.
                        AI akan mengenali penyakitnya beserta gejala, penyebab, dan cara penanganannya.
                    </p>
                </div>
            </div>

            <x-scanner-penyakit :action="route('petani.deteksi-penyakit.store')" :batal-url="route('dashboard')">
                @if ($lahans->isNotEmpty())
                    <div>
                        <label for="lahan_id" class="block text-sm font-medium text-gray-700 mb-1">Lahan Terkait (opsional)</label>
                        <select id="lahan_id" name="lahan_id" :disabled="sedangProses"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">-- Tidak terkait lahan tertentu --</option>
                            @foreach ($lahans as $l)
                                <option value="{{ $l->id }}" @selected(old('lahan_id') == $l->id)>
                                    {{ $l->nama_lahan }} @if ($l->tanaman_aktif) ({{ $l->tanaman_aktif }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-400">Jenis tanaman di lahan ini ikut dipakai AI sebagai petunjuk.</p>
                        @error('lahan_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </x-scanner-penyakit>
        </div>

        <aside class="lg:col-span-2 space-y-4">
            @include('partials.tips-foto-tanaman')

            <div class="bg-green-50 border border-green-200 rounded-2xl p-5 text-sm text-green-800">
                <h3 class="flex items-center gap-2 font-semibold mb-1">
                    <i data-lucide="users" class="w-4 h-4"></i> Diteruskan ke Penyuluh
                </h3>
                <p>Setiap hasil scan tersimpan di riwayat Anda dan otomatis bisa dilihat Petugas Penyuluh di wilayah Anda untuk ditindaklanjuti.</p>
            </div>
        </aside>
    </div>
</div>
@endsection
