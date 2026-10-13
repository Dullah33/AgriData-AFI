{{-- Tombol aksi di header dashboard. variant: primary | secondary --}}
@props(['href', 'icon' => null, 'variant' => 'primary'])

<a href="{{ $href }}" {{ $attributes->class([
    'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition',
    'focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 active:translate-y-px',
    'bg-brand-700 text-white shadow-sm hover:bg-brand-800' => $variant === 'primary',
    'bg-white text-stone-700 ring-1 ring-inset ring-stone-200 hover:bg-stone-50 hover:text-stone-900' => $variant !== 'primary',
]) }}>
    @if ($icon)
        <i data-lucide="{{ $icon }}" class="h-4 w-4"></i>
    @endif
    {{ $slot }}
</a>
