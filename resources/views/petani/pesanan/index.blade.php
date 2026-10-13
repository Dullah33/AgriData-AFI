@extends('layouts.dashboard')
@section('title', 'Pesanan Masuk')
@section('content')
@use('App\Models\Pembayaran')
@use('App\Models\Transaksi')

@php
    $tab = ['' => 'Semua'] + Transaksi::LABEL_STATUS;
@endphp

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-stone-900">Pesanan masuk</h1>
        <p class="mt-1 text-sm text-stone-500">Pesanan berstatus <span class="font-medium text-stone-700">Diproses</span> sudah dibayar dan siap Anda kirim.</p>
    </div>

    <x-flash />
    @error('alasan')
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</div>
    @enderror

    <nav class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Filter status">
        <ul class="flex gap-1 border-b border-stone-200">
            @foreach ($tab as $nilai => $label)
                @php
                    $aktif = (string) $status === (string) $nilai;
                    $jumlah = $nilai === '' ? $jumlahPerStatus->sum() : ($jumlahPerStatus[$nilai] ?? 0);
                @endphp
                <li>
                    <a href="{{ route('petani.pesanan.index', $nilai ? ['status' => $nilai] : []) }}" @if ($aktif) aria-current="page" @endif
                       @class([
                           '-mb-px flex items-center gap-2 whitespace-nowrap border-b-2 px-3 py-2.5 text-sm font-medium transition-colors',
                           'border-brand-600 text-brand-800' => $aktif,
                           'border-transparent text-stone-500 hover:text-stone-800' => ! $aktif,
                       ])>
                        {{ $label }}
                        <span @class(['rounded-full px-1.5 py-0.5 text-xs font-semibold tabular-nums', 'bg-brand-100 text-brand-800' => $aktif, 'bg-stone-100 text-stone-600' => ! $aktif])>{{ $jumlah }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    @if ($pesanans->isEmpty())
        <div class="rounded-2xl border border-stone-200/80 bg-white p-6 shadow-card">
            <x-dashboard.empty icon="inbox" title="Tidak ada pesanan" description="Pesanan untuk produk Anda akan muncul di sini."
                               :href="route('petani.produk.index')" action="Kelola produk" />
        </div>
    @else
        <ul class="space-y-3">
            @foreach ($pesanans as $p)
                @php
                    $bayar = $p->pembayaran;
                    $bolehTolak = $p->status_transaksi === Transaksi::MENUNGGU && (! $bayar || $bayar->status === Pembayaran::MENUNGGU_PEMBAYARAN);
                @endphp
                <li class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-semibold text-stone-900">{{ $p->produk->nama_produk }}</p>
                                <x-dashboard.status :status="$p->status_transaksi" />
                            </div>
                            <p class="mt-1 text-sm text-stone-600">
                                {{ $p->jumlah }} {{ $p->produk->satuan }} x Rp {{ number_format($p->harga_satuan ?? $p->produk->harga, 0, ',', '.') }}
                                = <span class="font-semibold text-stone-900">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</span>
                            </p>
                            <p class="mt-1 text-xs text-stone-500">
                                {{ $p->pembeli->username }} / {{ $p->created_at->locale('id')->diffForHumans() }}
                                @if ($bayar)
                                    / {{ $bayar->kode }}
                                @endif
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            @if ($p->status_transaksi === Transaksi::DIPROSES)
                                <form method="POST" action="{{ route('petani.pesanan.selesai', $p) }}" onsubmit="return confirm('Tandai pesanan ini sudah sampai ke pembeli?')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-700 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
                                        <i data-lucide="check" class="h-4 w-4"></i> Tandai selesai
                                    </button>
                                </form>
                            @elseif ($p->status_transaksi === Transaksi::MENUNGGU && ! $bayar)
                                <form method="POST" action="{{ route('petani.pesanan.konfirmasi', $p) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-brand-700 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">Konfirmasi</button>
                                </form>
                            @elseif ($p->status_transaksi === Transaksi::MENUNGGU && $bayar)
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-stone-500">
                                    <i data-lucide="hourglass" class="h-3.5 w-3.5"></i> {{ $bayar->labelStatus() }}
                                </span>
                            @endif

                            @if ($bolehTolak)
                                <details class="group relative" data-menu>
                                    <summary class="cursor-pointer list-none rounded-lg px-3.5 py-2 text-sm font-semibold text-red-700 ring-1 ring-inset ring-red-200 hover:bg-red-50 [&::-webkit-details-marker]:hidden">Tolak</summary>
                                    <form method="POST" action="{{ route('petani.pesanan.tolak', $p) }}"
                                          class="absolute right-0 z-20 mt-2 w-72 rounded-xl border border-stone-200 bg-white p-4 shadow-lift">
                                        @csrf
                                        <label for="alasan-{{ $p->id }}" class="block text-sm font-medium text-stone-700">Alasan penolakan</label>
                                        <textarea id="alasan-{{ $p->id }}" name="alasan" rows="3" required minlength="5" maxlength="300"
                                                  class="mt-1.5 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                                                  placeholder="Mis. stok di gudang rusak karena hujan"></textarea>
                                        <button type="submit" class="mt-3 w-full rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">Tolak & kembalikan stok</button>
                                    </form>
                                </details>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 rounded-xl bg-stone-50 px-4 py-3 text-sm sm:grid-cols-2">
                        <p class="text-stone-600"><span class="block text-xs font-medium text-stone-500">Alamat kirim</span>{{ $p->alamat_pengiriman ?? '-' }}</p>
                        <p class="text-stone-600"><span class="block text-xs font-medium text-stone-500">Catatan pembeli</span>{{ $p->catatan ?: '-' }}</p>
                    </div>

                    @if ($p->status_transaksi === Transaksi::BATAL && $p->alasan_batal)
                        <p class="mt-3 text-xs text-red-600">{{ $p->alasan_batal }}</p>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($pesanans->hasPages())
            <div>{{ $pesanans->links() }}</div>
        @endif
    @endif
</div>
@endsection
