{{-- Input jumlah dengan tombol minus/plus. Tetap berupa input number biasa kalau JS mati. --}}
@props(['name' => 'jumlah', 'value' => 1, 'min' => 1, 'max' => null, 'id' => null, 'label' => 'Jumlah'])

@php $id ??= $name.'-'.\Illuminate\Support\Str::random(4); @endphp

<div class="inline-flex items-center rounded-lg border border-stone-300 bg-white" data-stepper>
    <button type="button" data-langkah="-1" aria-label="Kurangi {{ strtolower($label) }}"
            class="flex h-10 w-10 items-center justify-center rounded-l-lg text-stone-600 hover:bg-stone-50 disabled:text-stone-300">
        <i data-lucide="minus" class="h-4 w-4"></i>
    </button>
    <label for="{{ $id }}" class="sr-only">{{ $label }}</label>
    <input id="{{ $id }}" type="number" name="{{ $name }}" value="{{ $value }}" min="{{ $min }}" @if ($max) max="{{ $max }}" @endif inputmode="numeric"
           {{ $attributes->class('h-10 w-16 border-x border-stone-300 text-center text-sm font-semibold tabular-nums focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-500 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none') }}>
    <button type="button" data-langkah="1" aria-label="Tambah {{ strtolower($label) }}"
            class="flex h-10 w-10 items-center justify-center rounded-r-lg text-stone-600 hover:bg-stone-50 disabled:text-stone-300">
        <i data-lucide="plus" class="h-4 w-4"></i>
    </button>
</div>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-stepper]').forEach((wadah) => {
                const input = wadah.querySelector('input');
                const tombol = wadah.querySelectorAll('[data-langkah]');
                const batas = () => [Number(input.min || 1), Number(input.max || Infinity)];
                const segarkan = () => {
                    const [min, max] = batas();
                    tombol[0].disabled = Number(input.value) <= min;
                    tombol[1].disabled = Number(input.value) >= max;
                };
                tombol.forEach((t) => t.addEventListener('click', () => {
                    const [min, max] = batas();
                    input.value = Math.min(max, Math.max(min, (Number(input.value) || min) + Number(t.dataset.langkah)));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    segarkan();
                }));
                input.addEventListener('input', segarkan);
                segarkan();
            });
        </script>
    @endpush
@endonce
