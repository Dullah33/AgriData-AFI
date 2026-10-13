{{-- Pesan sukses / gagal dari session (with('success') / with('error')) --}}
@if (session('success'))
    <div role="status" {{ $attributes->class('flex items-start gap-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-900') }}>
        <i data-lucide="circle-check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif
@if (session('error'))
    <div role="alert" {{ $attributes->class('flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800') }}>
        <i data-lucide="circle-alert" class="mt-0.5 h-4 w-4 shrink-0 text-red-600"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif
