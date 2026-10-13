@extends('layouts.dashboard')
@section('title', 'Dashboard Petani')
@section('content')

@php
    $aksiCepat = [
        ['label' => 'Analisis cuaca', 'desc' => 'Prakiraan & rekomendasi tanam', 'icon' => 'cloud-sun', 'route' => 'cuaca.index'],
        ['label' => 'Panduan budidaya', 'desc' => 'Cara tanam per komoditas', 'icon' => 'book-open', 'route' => 'budidaya.index'],
        ['label' => 'Lahan saya', 'desc' => 'Kelola petak & tanaman aktif', 'icon' => 'map', 'route' => 'petani.lahan.index'],
        ['label' => 'Artikel pertanian', 'desc' => 'Tips dari penyuluh', 'icon' => 'newspaper', 'route' => 'artikel.index'],
    ];
@endphp

<div class="space-y-8">
    <x-dashboard.page-header subtitle="Pantau lahan, kesehatan tanaman, dan penjualan hasil panen Anda.">
        <x-slot:actions>
            <x-dashboard.action :href="route('petani.produk.create')" icon="plus" variant="secondary">Tambah produk</x-dashboard.action>
            <x-dashboard.action :href="route('petani.deteksi-penyakit.create')" icon="scan-search">Scan penyakit</x-dashboard.action>
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
        <x-dashboard.stat label="Produk aktif" :value="$stats['produk_aktif']" icon="package" :href="route('petani.produk.index')"
                          hint="Listing yang bisa dibeli" />
        <x-dashboard.stat label="Perlu dikirim" :value="$stats['pesanan_perlu_dikirim']" icon="truck" :href="route('petani.pesanan.index', ['status' => 'diproses'])"
                          tone="attention" :hint="$stats['pesanan_perlu_dikirim'] > 0 ? 'Sudah dibayar pembeli' : 'Tidak ada pesanan baru'" />
        <x-dashboard.stat label="Petak lahan" :value="$stats['total_lahan']" icon="map" :href="route('petani.lahan.index')"
                          hint="Terdaftar di profil" />
        <x-dashboard.stat label="Kunjungan terjadwal" :value="$stats['kunjungan_terjadwal']" icon="calendar-check"
                          :href="route('petani.kunjungan-penyuluh.index')" hint="Dari penyuluh wilayah" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <x-dashboard.panel class="lg:col-span-3" title="Perlu dikirim" description="Pesanan yang sudah dibayar dan menunggu Anda kirim."
                           :href="route('petani.pesanan.index', ['status' => 'diproses'])">
            @if ($pesananTerbaru->isEmpty())
                <x-dashboard.empty icon="inbox" title="Belum ada pesanan untuk dikirim"
                                   description="Pastikan produk Anda punya foto dan stok supaya mudah ditemukan pembeli."
                                   :href="route('petani.produk.index')" action="Kelola produk" />
            @else
                <ul class="-my-1 divide-y divide-stone-100">
                    @foreach ($pesananTerbaru as $p)
                        <li class="flex items-center gap-3 py-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-stone-100 text-stone-500">
                                <i data-lucide="package" class="h-5 w-5"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-stone-900">{{ $p->produk->nama_produk }}</p>
                                <p class="truncate text-sm text-stone-500">
                                    {{ $p->pembeli->username }}
                                    <span class="text-stone-300" aria-hidden="true">/</span>
                                    {{ $p->jumlah }} {{ $p->produk->satuan }}
                                </p>
                            </div>
                            <x-dashboard.status :status="$p->status_transaksi" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dashboard.panel>

        <x-dashboard.panel class="lg:col-span-2" title="Hasil scan terbaru" :href="route('petani.deteksi-penyakit.index')" link-label="Riwayat">
            @if ($laporanTerbaru->isEmpty())
                <x-dashboard.empty icon="scan-search" title="Belum pernah scan"
                                   description="Foto daun yang sakit untuk tahu penyakit dan cara menanganinya."
                                   :href="route('petani.deteksi-penyakit.create')" action="Scan sekarang" />
            @else
                <ul class="-my-1 divide-y divide-stone-100">
                    @foreach ($laporanTerbaru as $laporan)
                        <li>
                            <a href="{{ route('petani.deteksi-penyakit.show', $laporan) }}"
                               class="-mx-2 flex items-center gap-3 rounded-lg px-2 py-3 transition-colors hover:bg-stone-50">
                                <img src="{{ Storage::url($laporan->foto_tanaman) }}" alt="" loading="lazy"
                                     class="h-12 w-12 shrink-0 rounded-lg bg-stone-100 object-cover">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-stone-900">{{ $laporan->nama_penyakit }}</p>
                                    <p class="text-sm text-stone-500">{{ $laporan->created_at->locale('id')->diffForHumans() }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $laporan->badgeRisiko() }}">
                                    {{ ucfirst($laporan->tingkat_risiko) }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dashboard.panel>
    </div>

    <section aria-labelledby="aksi-cepat">
        <h2 id="aksi-cepat" class="text-base font-semibold text-stone-900">Aksi cepat</h2>
        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($aksiCepat as $aksi)
                <a href="{{ route($aksi['route']) }}"
                   class="group flex items-center gap-3.5 rounded-2xl border border-stone-200/80 bg-white p-4 transition hover:border-brand-200 hover:bg-brand-50/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100 transition group-hover:bg-brand-700 group-hover:text-white group-hover:ring-brand-700">
                        <i data-lucide="{{ $aksi['icon'] }}" class="h-5 w-5"></i>
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-semibold text-stone-900">{{ $aksi['label'] }}</span>
                        <span class="block truncate text-sm text-stone-500">{{ $aksi['desc'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
</div>
@endsection
