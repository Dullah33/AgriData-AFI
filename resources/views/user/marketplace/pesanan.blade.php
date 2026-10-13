@extends('layouts.dashboard')
@section('title', 'Pesanan Saya')
@section('content')

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">Pesanan saya</h1>
            <p class="mt-1 text-sm text-stone-500">Pantau pembayaran dan status pengiriman pesanan Anda.</p>
        </div>
        <a href="{{ route('user.riwayat-ulasan') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-900">
            <i data-lucide="star" class="h-4 w-4"></i> Riwayat & ulasan
        </a>
    </div>

    <x-flash />

    @if ($pembayarans->isEmpty() && $pesananLama->isEmpty())
        <div class="rounded-2xl border border-stone-200/80 bg-white p-6 shadow-card">
            <x-dashboard.empty icon="shopping-basket" title="Belum ada pesanan"
                               description="Hasil panen segar dari petani lokal menunggu di marketplace."
                               :href="route('user.marketplace')" action="Mulai belanja" />
        </div>
    @endif

    <div class="space-y-4">
        @foreach ($pembayarans as $pembayaran)
            <article class="rounded-2xl border border-stone-200/80 bg-white shadow-card">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-100 px-5 py-4">
                    <div class="min-w-0">
                        <p class="flex flex-wrap items-center gap-2 text-sm font-semibold text-stone-900">
                            {{ $pembayaran->kode }}
                            <x-marketplace.status-pembayaran :pembayaran="$pembayaran" />
                        </p>
                        <p class="mt-0.5 text-xs text-stone-500">
                            {{ $pembayaran->created_at->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') }}
                            <span class="text-stone-300" aria-hidden="true">/</span> {{ $pembayaran->labelMetode() }}
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <p class="text-right">
                            <span class="block text-xs text-stone-500">Total</span>
                            <span class="text-sm font-semibold text-stone-900 tabular-nums">Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</span>
                        </p>
                        <a href="{{ route('user.pembayaran.show', $pembayaran) }}" @class([
                            'inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-semibold transition',
                            'bg-brand-700 text-white shadow-sm hover:bg-brand-800' => $pembayaran->bisaDibayar(),
                            'bg-white text-stone-700 ring-1 ring-inset ring-stone-200 hover:bg-stone-50' => ! $pembayaran->bisaDibayar(),
                        ])>
                            {{ $pembayaran->bisaDibayar() ? 'Bayar' : 'Detail' }} <i data-lucide="arrow-right" class="h-4 w-4"></i>
                        </a>
                    </div>
                </header>
                <ul class="divide-y divide-stone-100 px-5">
                    @foreach ($pembayaran->transaksis as $t)
                        <li class="flex items-center justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-stone-900">{{ $t->produk->nama_produk ?? 'Produk' }}</p>
                                <p class="text-xs text-stone-500">{{ $t->jumlah }} {{ $t->produk->satuan ?? '' }} dari {{ $t->produk->petani->username ?? '-' }}</p>
                            </div>
                            <x-dashboard.status :status="$t->status_transaksi" />
                        </li>
                    @endforeach
                </ul>
            </article>
        @endforeach
    </div>

    @if ($pembayarans->hasPages())
        <div>{{ $pembayarans->links() }}</div>
    @endif

    @if ($pesananLama->isNotEmpty())
        <x-dashboard.panel title="Pesanan sebelumnya" description="Dibuat sebelum ada fitur pembayaran online, dikonfirmasi langsung oleh petani.">
            <ul class="-my-1 divide-y divide-stone-100">
                @foreach ($pesananLama as $t)
                    <li class="flex items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-stone-900">{{ $t->produk->nama_produk ?? 'Produk' }}</p>
                            <p class="text-xs text-stone-500">{{ $t->jumlah }} {{ $t->produk->satuan ?? '' }} / Rp {{ number_format($t->total_harga, 0, ',', '.') }}</p>
                        </div>
                        <x-dashboard.status :status="$t->status_transaksi" />
                    </li>
                @endforeach
            </ul>
        </x-dashboard.panel>
    @endif
</div>
@endsection
