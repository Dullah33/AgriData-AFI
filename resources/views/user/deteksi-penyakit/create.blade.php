@extends('layouts.dashboard')
@section('title', 'AI Scanner Penyakit Tanaman (Terbatas)')
@section('content')

<div class="max-w-5xl">
    <div class="grid gap-6 lg:grid-cols-5">
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm p-5 sm:p-6">
            <div class="flex items-start gap-3 mb-6">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-green-100 text-green-700 flex items-center justify-center">
                    <i data-lucide="scan-search" class="w-6 h-6"></i>
                </span>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Scan Penyakit Tanaman</h2>
                    <p class="text-sm text-gray-500">
                        Versi edukatif untuk masyarakat umum. Foto tanaman lewat kamera atau unggah dari galeri
                        untuk mengenali indikasi penyakitnya.
                    </p>
                </div>
            </div>

            <x-scanner-penyakit :action="route('user.deteksi-penyakit.store')" />
        </div>

        <aside class="lg:col-span-2 space-y-4">
            @include('partials.tips-foto-tanaman')

            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-sm text-amber-800">
                <h3 class="flex items-center gap-2 font-semibold mb-1">
                    <i data-lucide="info" class="w-4 h-4"></i> Fitur edukatif
                </h3>
                <p>Untuk penanganan lahan pertanian sungguhan, gunakan akun Petani agar hasil deteksi bisa ditindaklanjuti oleh Petugas Penyuluh.</p>
            </div>
        </aside>
    </div>
</div>
@endsection
