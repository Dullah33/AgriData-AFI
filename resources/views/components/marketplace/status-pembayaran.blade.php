{{-- Badge status pembayaran --}}
@props(['pembayaran'])

@php
    $gaya = match ($pembayaran->status) {
        \App\Models\Pembayaran::MENUNGGU_PEMBAYARAN => 'bg-amber-50 text-amber-800 ring-amber-200/80',
        \App\Models\Pembayaran::MENUNGGU_VERIFIKASI => 'bg-blue-50 text-blue-800 ring-blue-200/80',
        \App\Models\Pembayaran::LUNAS => 'bg-brand-50 text-brand-800 ring-brand-200/80',
        \App\Models\Pembayaran::GAGAL => 'bg-red-50 text-red-700 ring-red-200/80',
        default => 'bg-stone-100 text-stone-700 ring-stone-200',
    };
@endphp

<span {{ $attributes->class(['inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset', $gaya]) }}>
    {{ $pembayaran->labelStatus() }}
</span>
