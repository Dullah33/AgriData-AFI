@extends('layouts.dashboard')
@section('title', 'Dashboard Admin')
@section('content')

@php
    $persenVerifikasi = $stats['total_petani'] > 0
        ? round($stats['petani_terverifikasi'] / $stats['total_petani'] * 100)
        : 0;
@endphp

<div class="space-y-8">
    <x-dashboard.page-header subtitle="Ringkasan aktivitas platform AgriScan hari ini.">
        <x-slot:actions>
            <x-dashboard.action :href="route('admin.peta-lahan.index')" icon="map" variant="secondary">Lihat peta</x-dashboard.action>
            <x-dashboard.action :href="route('admin.artikel.create')" icon="pen-line">Tulis artikel</x-dashboard.action>
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
        <x-dashboard.stat label="Total petani" :value="$stats['total_petani']" icon="users" :href="route('admin.petani.index')"
                          :hint="$stats['petani_terverifikasi'].' sudah terverifikasi'">
            <div class="mt-4 flex items-center gap-3">
                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-stone-100" role="progressbar"
                     aria-valuenow="{{ $persenVerifikasi }}" aria-valuemin="0" aria-valuemax="100" aria-label="Petani terverifikasi">
                    <div class="h-full rounded-full bg-brand-500" style="width: {{ $persenVerifikasi }}%"></div>
                </div>
                <span class="text-xs font-semibold text-stone-500 tabular-nums">{{ $persenVerifikasi }}%</span>
            </div>
        </x-dashboard.stat>
        <x-dashboard.stat label="Transaksi bulan ini" :value="$stats['transaksi_bulan_ini']" icon="shopping-basket"
                          :href="route('admin.pembayaran.index')" :tone="$stats['pembayaran_menunggu'] > 0 ? 'attention' : 'brand'"
                          :hint="$stats['pembayaran_menunggu'] > 0 ? $stats['pembayaran_menunggu'].' pembayaran perlu diverifikasi' : $stats['transaksi_pending'].' masih menunggu'" />
        <x-dashboard.stat label="Penyuluh aktif" :value="$stats['penyuluh_aktif']" icon="user-check" :href="route('admin.penyuluh.index')"
                          hint="Petugas lapangan" />
        <x-dashboard.stat label="Artikel terbit" :value="$stats['artikel_published']" icon="newspaper" :href="route('admin.artikel.index')"
                          :hint="$stats['artikel_draft'].' masih draf'" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <x-dashboard.panel class="lg:col-span-3" title="Menunggu verifikasi" :count="$petaniBelumVerifikasi->count() ?: null"
                           description="Petani baru yang datanya perlu dicek sebelum bisa berjualan."
                           :href="route('admin.petani.index')">
            @if ($petaniBelumVerifikasi->isEmpty())
                <x-dashboard.empty icon="shield-check" title="Semua petani sudah terverifikasi"
                                   description="Petani yang baru mendaftar akan muncul di sini." />
            @else
                <ul class="-my-1 divide-y divide-stone-100">
                    @foreach ($petaniBelumVerifikasi as $p)
                        <li class="flex items-center gap-3 py-3">
                            <x-dashboard.avatar :name="$p->user->username" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-stone-900">{{ $p->user->username }}</p>
                                <p class="truncate text-sm text-stone-500">
                                    {{ $p->komoditas_utama ?: 'Komoditas belum diisi' }}
                                    <span class="text-stone-300" aria-hidden="true">/</span>
                                    daftar {{ $p->created_at->locale('id')->diffForHumans() }}
                                </p>
                            </div>
                            <a href="{{ route('admin.petani.show', $p) }}"
                               class="shrink-0 rounded-lg bg-white px-3 py-1.5 text-sm font-semibold text-stone-700 ring-1 ring-inset ring-stone-200 transition hover:bg-brand-50 hover:text-brand-800 hover:ring-brand-200">
                                Tinjau
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dashboard.panel>

        <x-dashboard.panel class="lg:col-span-2" title="Ulasan terbaru" :href="route('admin.ulasan.index')" link-label="Moderasi">
            @if ($ulasanTerbaru->isEmpty())
                <x-dashboard.empty icon="message-square" title="Belum ada ulasan"
                                   description="Ulasan pembeli untuk hasil panen akan tampil di sini." />
            @else
                <ul class="-my-1 divide-y divide-stone-100">
                    @foreach ($ulasanTerbaru as $u)
                        <li class="py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-0.5" aria-label="Rating {{ $u->rating }} dari 5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i data-lucide="star" @class(['h-4 w-4', 'fill-amber-400 text-amber-400' => $i <= $u->rating, 'text-stone-200' => $i > $u->rating])></i>
                                    @endfor
                                </div>
                                <span class="text-xs text-stone-400">{{ $u->created_at->locale('id')->diffForHumans() }}</span>
                            </div>
                            <p class="mt-1.5 line-clamp-2 text-sm text-stone-600">{{ $u->komentar ?: 'Tanpa komentar' }}</p>
                            @if ($u->transaksi?->produk)
                                <p class="mt-1 truncate text-xs font-medium text-stone-400">{{ $u->transaksi->produk->nama_produk }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dashboard.panel>
    </div>
</div>
@endsection
