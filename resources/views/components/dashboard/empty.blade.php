{{-- Empty state untuk panel yang belum berisi data --}}
@props(['icon', 'title', 'description' => null, 'href' => null, 'action' => null])

<div class="flex flex-col items-center rounded-xl border border-dashed border-stone-200 bg-stone-50/60 px-6 py-10 text-center">
    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-stone-400 ring-1 ring-stone-200">
        <i data-lucide="{{ $icon }}" class="h-5 w-5"></i>
    </span>
    <p class="mt-3 text-sm font-semibold text-stone-700">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 max-w-xs text-sm text-stone-500">{{ $description }}</p>
    @endif
    @if ($href && $action)
        <a href="{{ $href }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-900">
            {{ $action }} <i data-lucide="arrow-right" class="h-4 w-4"></i>
        </a>
    @endif
</div>
