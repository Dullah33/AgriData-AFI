@extends('layouts.dashboard')
@section('title', $produk->nama_produk)
@section('content')

@php
    $sisaBoleh = max(0, $produk->stok - $diKeranjang);
@endphp

<div class="space-y-6">
    <a href="{{ route('user.marketplace') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-500 hover:text-stone-900">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Kembali ke marketplace
    </a>

    <x-flash />

    <div class="grid gap-8 lg:grid-cols-2">
        <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white">
            @if ($produk->foto_produk)
                <img src="{{ Storage::url($produk->foto_produk) }}" alt="{{ $produk->nama_produk }}" class="aspect-[4/3] w-full object-cover">
            @else
                <div class="flex aspect-[4/3] w-full items-center justify-center bg-stone-100 text-stone-400">
                    <i data-lucide="wheat" class="h-10 w-10"></i>
                </div>
            @endif
        </div>

        <div class="flex flex-col">
            @if ($produk->kategori)
                <p class="text-sm font-medium text-brand-700">{{ $produk->kategori }}</p>
            @endif
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl">{{ $produk->nama_produk }}</h1>

            <div class="mt-3 flex items-center gap-2.5 text-sm text-stone-600">
                <x-dashboard.avatar :name="$produk->petani->username" size="h-7 w-7" />
                <span>Dijual oleh <span class="font-semibold text-stone-900">{{ $produk->petani->username }}</span></span>
            </div>

            <div class="mt-6 flex flex-wrap items-end gap-x-6 gap-y-2 border-b border-stone-200/80 pb-6">
                <p>
                    <span class="text-3xl font-bold tracking-tight text-stone-900 tabular-nums">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                    <span class="text-sm text-stone-500">/ {{ $produk->satuan }}</span>
                </p>
                <span @class([
                    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset',
                    'bg-brand-50 text-brand-800 ring-brand-200/80' => $produk->stok > 0,
                    'bg-red-50 text-red-700 ring-red-200/80' => $produk->stok < 1,
                ])>
                    <i data-lucide="package" class="h-3.5 w-3.5"></i>
                    {{ $produk->stok > 0 ? 'Stok '.$produk->stok.' '.$produk->satuan : 'Stok habis' }}
                </span>
            </div>

            <p class="mt-6 whitespace-pre-line text-[15px] leading-relaxed text-stone-600">{{ $produk->deskripsi }}</p>

            <div class="mt-8 rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card">
                @if ($produk->stok < 1)
                    <p class="text-sm text-stone-600">Produk ini sedang habis. Coba lagi nanti atau lihat produk lain di marketplace.</p>
                @elseif ($sisaBoleh < 1)
                    <p class="text-sm text-stone-600">Seluruh stok yang tersedia sudah ada di keranjang Anda.</p>
                    <a href="{{ route('user.keranjang.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-800">
                        <i data-lucide="shopping-cart" class="h-4 w-4"></i> Buka keranjang
                    </a>
                @else
                    <form method="POST" action="{{ route('user.keranjang.store', $produk) }}">
                        @csrf
                        <label class="block text-sm font-medium text-stone-700">Jumlah ({{ $produk->satuan }})</label>
                        <div class="mt-2 flex flex-wrap items-center gap-4">
                            <x-marketplace.stepper :value="old('jumlah', 1)" :max="$sisaBoleh" label="Jumlah" />
                            <p class="text-sm text-stone-500">
                                Maks. {{ $sisaBoleh }} {{ $produk->satuan }}
                                @if ($diKeranjang > 0)
                                    <span class="block text-xs">({{ $diKeranjang }} sudah di keranjang)</span>
                                @endif
                            </p>
                        </div>
                        @error('jumlah')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <button type="submit" name="aksi" value="keranjang"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-semibold text-stone-800 ring-1 ring-inset ring-stone-300 transition hover:bg-stone-50 active:translate-y-px">
                                <i data-lucide="shopping-cart" class="h-4 w-4"></i> Tambah ke keranjang
                            </button>
                            <button type="submit" name="aksi" value="beli"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800 active:translate-y-px">
                                Beli sekarang
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
