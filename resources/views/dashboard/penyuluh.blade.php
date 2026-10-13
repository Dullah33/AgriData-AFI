@extends('layouts.dashboard')
@section('title', 'Dashboard Penyuluh')
@section('content')

@php
    $pintasan = [
        ['label' => 'Wilayah binaan', 'icon' => 'map-pin', 'route' => 'penyuluh.wilayah-binaan.index'],
        ['label' => 'Pelatihan kelompok tani', 'icon' => 'graduation-cap', 'route' => 'penyuluh.pelatihan.index'],
        ['label' => 'Laporan kunjungan', 'icon' => 'clipboard-list', 'route' => 'penyuluh.kunjungan.riwayat'],
        ['label' => 'Analisis cuaca wilayah', 'icon' => 'cloud-sun', 'route' => 'cuaca.index'],
    ];
@endphp

<div class="space-y-8">
    <x-dashboard.page-header :subtitle="$officer ? 'Wilayah binaan: '.$officer->wilayah_binaan : 'Wilayah binaan belum ditetapkan.'">
        @if ($officer)
            <x-slot:actions>
                <x-dashboard.action :href="route('penyuluh.laporan-bulanan.index')" icon="file-text" variant="secondary">Laporan bulanan</x-dashboard.action>
                <x-dashboard.action :href="route('penyuluh.kunjungan.create')" icon="calendar-plus">Jadwalkan kunjungan</x-dashboard.action>
            </x-slot:actions>
        @endif
    </x-dashboard.page-header>

    @if (! $officer)
        <div class="flex gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-5 sm:p-6">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                <i data-lucide="circle-alert" class="h-5 w-5"></i>
            </span>
            <div>
                <h2 class="text-sm font-semibold text-amber-900">Akun Anda belum punya wilayah binaan</h2>
                <p class="mt-1 text-sm text-amber-800">
                    Admin perlu menetapkan wilayah binaan sebelum Anda bisa melihat petani, menjadwalkan kunjungan,
                    dan menindaklanjuti laporan penyakit. Silakan hubungi Admin dinas.
                </p>
            </div>
        </div>
    @else
        <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            <x-dashboard.stat label="Petani binaan" :value="$stats['petani_binaan']" icon="users" :href="route('penyuluh.wilayah-binaan.index')"
                              hint="Di wilayah Anda" />
            <x-dashboard.stat label="Kunjungan bulan ini" :value="$stats['kunjungan_bulan_ini']" icon="calendar-check"
                              :href="route('penyuluh.kunjungan.riwayat')" :hint="$stats['kunjungan_terjadwal'].' lagi terjadwal'" />
            <x-dashboard.stat label="Pelatihan bulan ini" :value="$stats['pelatihan_bulan_ini']" icon="graduation-cap"
                              :href="route('penyuluh.pelatihan.index')" hint="Kegiatan kelompok tani" />
            <x-dashboard.stat label="Laporan penyakit baru" :value="$stats['laporan_penyakit_baru']" icon="scan-search"
                              :href="route('penyuluh.deteksi-penyakit.index')" tone="attention"
                              :hint="$stats['laporan_penyakit_baru'] > 0 ? 'Menunggu ditinjau' : 'Semua sudah ditinjau'" />
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <x-dashboard.panel class="lg:col-span-2" title="Kunjungan mendatang" :href="route('penyuluh.kunjungan.index')">
                @if ($kunjunganMendatang->isEmpty())
                    <x-dashboard.empty icon="calendar-days" title="Belum ada jadwal kunjungan"
                                       description="Jadwalkan kunjungan lapangan ke petani binaan Anda."
                                       :href="route('penyuluh.kunjungan.create')" action="Jadwalkan kunjungan" />
                @else
                    <ul class="space-y-2">
                        @foreach ($kunjunganMendatang as $k)
                            @php $tanggal = $k->tanggal_kunjungan->locale('id'); @endphp
                            <li class="flex items-center gap-4 rounded-xl border border-stone-100 p-3 transition-colors hover:border-stone-200 hover:bg-stone-50/60">
                                <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-lg bg-brand-50 ring-1 ring-inset ring-brand-100">
                                    <span class="text-[11px] font-semibold uppercase text-brand-700">{{ $tanggal->translatedFormat('M') }}</span>
                                    <span class="text-xl font-bold leading-none text-brand-900 tabular-nums">{{ $tanggal->format('j') }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-stone-900">{{ $k->petaniProfile->user->username ?? 'Petani' }}</p>
                                    <p class="text-sm text-stone-500">
                                        {{ $tanggal->translatedFormat('l') }},
                                        {{ $tanggal->isToday() ? 'hari ini' : ($tanggal->isTomorrow() ? 'besok' : $tanggal->diffForHumans()) }}
                                    </p>
                                </div>
                                <span class="hidden shrink-0 rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-semibold text-sky-800 ring-1 ring-inset ring-sky-200/80 sm:inline-flex">Terjadwal</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-dashboard.panel>

            <x-dashboard.panel title="Pintasan">
                <ul class="-mx-2 space-y-0.5">
                    @foreach ($pintasan as $item)
                        <li>
                            <a href="{{ route($item['route']) }}"
                               class="group flex items-center gap-3 rounded-lg px-2 py-2.5 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50 hover:text-stone-900">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100">
                                    <i data-lucide="{{ $item['icon'] }}" class="h-4 w-4"></i>
                                </span>
                                <span class="flex-1">{{ $item['label'] }}</span>
                                <i data-lucide="chevron-right" class="h-4 w-4 text-stone-300 transition group-hover:translate-x-0.5 group-hover:text-stone-500"></i>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </x-dashboard.panel>
        </div>
    @endif
</div>
@endsection
