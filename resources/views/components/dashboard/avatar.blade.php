{{-- Avatar inisial nama --}}
@props(['name', 'size' => 'h-9 w-9'])

<span {{ $attributes->class([$size, 'flex shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-800']) }}>
    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($name, 0, 2)) }}
</span>
