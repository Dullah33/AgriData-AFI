{{-- Header halaman dashboard: tanggal, sapaan sesuai jam (WIB), subjudul, dan tombol aksi --}}
@props(['subtitle' => null])

@php
    $sekarang = now('Asia/Jakarta')->locale('id');
    $salam = match (true) {
        $sekarang->hour < 11 => 'Selamat pagi',
        $sekarang->hour < 15 => 'Selamat siang',
        $sekarang->hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };
@endphp

<div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        <p class="text-sm font-medium text-brand-700">{{ $sekarang->translatedFormat('l, j F Y') }}</p>
        <h1 class="mt-1.5 text-2xl font-bold tracking-tight text-stone-900 sm:text-[28px]">
            {{ $salam }}, {{ Auth::user()->username }}
        </h1>
        @if ($subtitle)
            <p class="mt-1.5 text-[15px] text-stone-500">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap gap-2.5">{{ $actions }}</div>
    @endisset
</div>
