{{--
    Kartu statistik. tone "attention" dipakai untuk angka yang butuh tindakan
    (mis. pesanan menunggu) dan hanya aktif kalau nilainya lebih dari nol.
--}}
@props(['label', 'value', 'icon', 'hint' => null, 'href' => null, 'tone' => 'brand'])

@php
    $perluTindakan = $tone === 'attention' && (int) $value > 0;
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class([
        'group relative flex flex-col rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card sm:p-5',
        'transition duration-200 hover:-translate-y-0.5 hover:border-stone-300/80 hover:shadow-lift focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500' => $href,
    ]) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-[13px] font-medium leading-snug text-stone-500 sm:text-sm">{{ $label }}</p>
        <span @class([
            'hidden h-9 w-9 shrink-0 sm:flex items-center justify-center rounded-lg ring-1 ring-inset',
            'bg-amber-50 text-amber-700 ring-amber-200/70' => $perluTindakan,
            'bg-brand-50 text-brand-700 ring-brand-100' => ! $perluTindakan,
        ])>
            <i data-lucide="{{ $icon }}" class="h-[18px] w-[18px]"></i>
        </span>
    </div>
    <p class="mt-2 text-2xl font-bold tracking-tight text-stone-900 tabular-nums sm:mt-3 sm:text-3xl">{{ $value }}</p>
    @if ($hint)
        <p @class(['mt-1 text-xs sm:text-sm', 'font-medium text-amber-700' => $perluTindakan, 'text-stone-500' => ! $perluTindakan])>{{ $hint }}</p>
    @endif
    {{ $slot }}
</{{ $tag }}>
