{{-- Panel berjudul untuk daftar di dashboard --}}
@props(['title', 'description' => null, 'href' => null, 'linkLabel' => 'Lihat semua', 'count' => null])

<section {{ $attributes->class('flex flex-col rounded-2xl border border-stone-200/80 bg-white shadow-card') }}>
    <header class="flex items-start justify-between gap-4 px-5 pt-5 sm:px-6">
        <div class="min-w-0">
            <h2 class="flex items-center gap-2 text-base font-semibold text-stone-900">
                {{ $title }}
                @if ($count)
                    <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs font-semibold text-stone-600 tabular-nums">{{ $count }}</span>
                @endif
            </h2>
            @if ($description)
                <p class="mt-0.5 text-sm text-stone-500">{{ $description }}</p>
            @endif
        </div>
        @if ($href)
            <a href="{{ $href }}"
               class="inline-flex shrink-0 items-center gap-1 rounded-md text-sm font-semibold text-brand-700 transition-colors hover:text-brand-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                {{ $linkLabel }} <i data-lucide="arrow-right" class="h-4 w-4"></i>
            </a>
        @endif
    </header>
    <div class="flex-1 px-5 pb-5 pt-4 sm:px-6">
        {{ $slot }}
    </div>
</section>
