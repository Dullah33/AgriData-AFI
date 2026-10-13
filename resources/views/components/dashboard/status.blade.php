{{-- Badge status transaksi --}}
@props(['status'])

@php
    $gaya = [
        'pending'  => ['Menunggu', 'bg-amber-50 text-amber-800 ring-amber-200/80'],
        'diproses' => ['Diproses', 'bg-sky-50 text-sky-800 ring-sky-200/80'],
        'selesai'  => ['Selesai', 'bg-brand-50 text-brand-800 ring-brand-200/80'],
        'batal'    => ['Dibatalkan', 'bg-red-50 text-red-700 ring-red-200/80'],
        'ditolak'  => ['Ditolak', 'bg-red-50 text-red-700 ring-red-200/80'],
    ][$status] ?? [\Illuminate\Support\Str::headline($status), 'bg-stone-100 text-stone-700 ring-stone-200'];
@endphp

<span {{ $attributes->class(['inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset', $gaya[1]]) }}>
    {{ $gaya[0] }}
</span>
