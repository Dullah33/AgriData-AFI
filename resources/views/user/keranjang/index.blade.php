@extends('layouts.dashboard')
@section('title', 'Keranjang')
@section('content')

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">Keranjang</h1>
            <p class="mt-1 text-sm text-stone-500">
                {{ $items->isEmpty() ? 'Belum ada produk di keranjang.' : $items->count().' produk dari '.$perPetani->count().' petani' }}
            </p>
        </div>
        <a href="{{ route('user.marketplace') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-900">
            <i data-lucide="plus" class="h-4 w-4"></i> Tambah produk lain
        </a>
    </div>

    <x-flash />

    @if ($items->isEmpty())
        <div class="rounded-2xl border border-stone-200/80 bg-white p-6 shadow-card">
            <x-dashboard.empty icon="shopping-cart" title="Keranjang masih kosong"
                               description="Pilih hasil panen segar langsung dari petani di marketplace."
                               :href="route('user.marketplace')" action="Buka marketplace" />
        </div>
    @else
        <div class="grid items-start gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                @foreach ($perPetani as $petani => $kelompok)
                    <section class="rounded-2xl border border-stone-200/80 bg-white shadow-card">
                        <header class="flex items-center gap-2.5 border-b border-stone-100 px-5 py-3.5">
                            <x-dashboard.avatar :name="$petani" size="h-7 w-7" />
                            <p class="text-sm font-semibold text-stone-900">{{ $petani }}</p>
                        </header>
                        <ul class="divide-y divide-stone-100">
                            @foreach ($kelompok as $item)
                                @php $masalah = $item->masalahStok(); $produk = $item->produk; @endphp
                                <li class="flex gap-4 p-5">
                                    @if ($produk?->foto_produk)
                                        <img src="{{ Storage::url($produk->foto_produk) }}" alt="" class="h-20 w-20 shrink-0 rounded-xl bg-stone-100 object-cover">
                                    @else
                                        <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                            <i data-lucide="wheat" class="h-6 w-6"></i>
                                        </span>
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <a href="{{ $produk ? route('user.marketplace.show', $produk) : '#' }}" class="block truncate text-sm font-semibold text-stone-900 hover:text-brand-800">
                                                    {{ $produk->nama_produk ?? 'Produk tidak tersedia' }}
                                                </a>
                                                <p class="text-sm text-stone-500">Rp {{ number_format($produk->harga ?? 0, 0, ',', '.') }} / {{ $produk->satuan ?? '-' }}</p>
                                            </div>
                                            <form method="POST" action="{{ route('user.keranjang.destroy', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" aria-label="Hapus {{ $produk->nama_produk ?? 'produk' }} dari keranjang"
                                                        class="-m-1.5 rounded-lg p-1.5 text-stone-400 transition hover:bg-red-50 hover:text-red-600">
                                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                </button>
                                            </form>
                                        </div>

                                        @if ($masalah)
                                            <p class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-800">
                                                <i data-lucide="circle-alert" class="h-3.5 w-3.5"></i> {{ $masalah }}
                                            </p>
                                        @endif

                                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                            @if ($produk && $produk->bisaDibeli())
                                                <form method="POST" action="{{ route('user.keranjang.update', $item) }}" data-ubah-jumlah>
                                                    @csrf
                                                    @method('PATCH')
                                                    {{-- Penanda supaya pesan error tampil di item yang benar --}}
                                                    <input type="hidden" name="_item" value="{{ $item->id }}">
                                                    <x-marketplace.stepper :value="$item->jumlah" :max="$produk->stok" :id="'jumlah-'.$item->id" label="Jumlah {{ $produk->nama_produk }}" />
                                                    <noscript><button type="submit" class="ml-2 text-sm font-semibold text-brand-700">Simpan</button></noscript>
                                                </form>
                                            @else
                                                <span class="text-sm text-stone-500">Jumlah: {{ $item->jumlah }}</span>
                                            @endif
                                            <p class="text-sm font-semibold text-stone-900 tabular-nums">Rp {{ number_format($item->subtotal(), 0, ',', '.') }}</p>
                                        </div>
                                        @if ($errors->has('jumlah') && old('_item') == $item->id)
                                            <p class="mt-2 text-sm text-red-600">{{ $errors->first('jumlah') }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>

            <aside class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card lg:sticky lg:top-24">
                <h2 class="text-base font-semibold text-stone-900">Ringkasan</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between text-stone-600">
                        <dt>Total harga ({{ $items->sum('jumlah') }} barang)</dt>
                        <dd class="tabular-nums">Rp {{ number_format($total, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-stone-100 pt-3 text-base font-semibold text-stone-900">
                        <dt>Total</dt>
                        <dd class="tabular-nums">Rp {{ number_format($total, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                @if ($errors->has('jumlah') && ! old('_item'))
                    <p class="mt-3 text-sm text-red-600">{{ $errors->first('jumlah') }}</p>
                @endif

                @if ($adaMasalah)
                    <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">Perbaiki jumlah produk yang ditandai sebelum checkout.</p>
                    <span class="mt-4 flex w-full cursor-not-allowed items-center justify-center rounded-lg bg-stone-200 px-4 py-3 text-sm font-semibold text-stone-500">Lanjut ke checkout</span>
                @else
                    <a href="{{ route('user.checkout.create') }}"
                       class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800 active:translate-y-px">
                        Lanjut ke checkout <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                @endif
                <p class="mt-3 text-center text-xs text-stone-500">Stok baru dipesan saat checkout.</p>
            </aside>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        // Simpan jumlah otomatis setelah pembeli berhenti mengubah selama 0,6 detik
        document.querySelectorAll('form[data-ubah-jumlah]').forEach((form) => {
            let jeda;
            form.addEventListener('change', () => {
                clearTimeout(jeda);
                jeda = setTimeout(() => form.requestSubmit(), 600);
            });
        });
    </script>
@endpush
@endsection
