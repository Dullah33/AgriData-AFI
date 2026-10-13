@extends('layouts.dashboard')
@section('title', 'Pembayaran '.$pembayaran->kode)
@section('content')
@use('App\Models\Pembayaran')

@php
    $buktiPdf = $pembayaran->bukti_transfer && str_ends_with(strtolower($pembayaran->bukti_transfer), '.pdf');
    $waktu = fn ($t) => $t?->copy()->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i').' WIB';
@endphp

<div class="space-y-6">
    <div>
        <a href="{{ route('admin.pembayaran.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-500 hover:text-stone-900">
            <i data-lucide="arrow-left" class="h-4 w-4"></i> Semua pembayaran
        </a>
        <h1 class="mt-3 flex flex-wrap items-center gap-3 text-2xl font-bold tracking-tight text-stone-900">
            {{ $pembayaran->kode }} <x-marketplace.status-pembayaran :pembayaran="$pembayaran" />
        </h1>
    </div>

    <x-flash />
    @error('alasan')
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</div>
    @enderror

    <div class="grid items-start gap-6 lg:grid-cols-5">
        {{-- Bukti transfer --}}
        <section class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card lg:col-span-3">
            <header class="flex items-center justify-between border-b border-stone-100 px-5 py-3.5">
                <h2 class="text-sm font-semibold text-stone-900">Bukti transfer</h2>
                @if ($pembayaran->bukti_transfer)
                    <a href="{{ route('admin.pembayaran.bukti', $pembayaran) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-900">
                        Buka ukuran penuh <i data-lucide="external-link" class="h-3.5 w-3.5"></i>
                    </a>
                @endif
            </header>
            @if (! $pembayaran->bukti_transfer)
                <div class="p-6">
                    <x-dashboard.empty icon="file-image" title="Belum ada bukti transfer"
                                       :description="$pembayaran->metode === Pembayaran::METODE_MIDTRANS ? 'Pembayaran Midtrans diverifikasi otomatis lewat notifikasi.' : 'Pembeli belum mengunggah bukti.'" />
                </div>
            @elseif ($buktiPdf)
                <iframe src="{{ route('admin.pembayaran.bukti', $pembayaran) }}" title="Bukti transfer {{ $pembayaran->kode }}" class="h-[560px] w-full bg-stone-100"></iframe>
            @else
                <div class="bg-stone-100 p-4">
                    <img src="{{ route('admin.pembayaran.bukti', $pembayaran) }}" alt="Bukti transfer {{ $pembayaran->kode }}" class="mx-auto max-h-[560px] rounded-lg object-contain">
                </div>
            @endif
        </section>

        {{-- Rincian & aksi --}}
        <div class="space-y-4 lg:col-span-2">
            <section class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card">
                <p class="text-sm font-medium text-stone-500">Nominal yang harus diterima</p>
                <p class="mt-1 text-3xl font-bold tracking-tight text-stone-900 tabular-nums">Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</p>
                <dl class="mt-4 space-y-2 border-t border-stone-100 pt-4 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-stone-500">Pembeli</dt><dd class="font-medium text-stone-900">{{ $pembayaran->pembeli->username }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-stone-500">Email</dt><dd class="truncate text-stone-900">{{ $pembayaran->pembeli->email }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-stone-500">Metode</dt><dd class="text-stone-900">{{ $pembayaran->labelMetode() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-stone-500">Dibuat</dt><dd class="text-stone-900">{{ $waktu($pembayaran->created_at) }}</dd></div>
                    @if ($pembayaran->bukti_diunggah_at)
                        <div class="flex justify-between gap-3"><dt class="text-stone-500">Bukti dikirim</dt><dd class="text-stone-900">{{ $waktu($pembayaran->bukti_diunggah_at) }}</dd></div>
                    @endif
                    @if ($pembayaran->dibayar_at)
                        <div class="flex justify-between gap-3"><dt class="text-stone-500">Lunas</dt><dd class="text-stone-900">{{ $waktu($pembayaran->dibayar_at) }}</dd></div>
                    @endif
                    @if ($pembayaran->verifikator)
                        <div class="flex justify-between gap-3"><dt class="text-stone-500">Diperiksa oleh</dt><dd class="text-stone-900">{{ $pembayaran->verifikator->username }}</dd></div>
                    @endif
                    @if ($pembayaran->gateway_transaction_id)
                        <div class="flex justify-between gap-3"><dt class="text-stone-500">ID Midtrans</dt><dd class="truncate font-mono text-xs text-stone-900">{{ $pembayaran->gateway_transaction_id }}</dd></div>
                    @endif
                </dl>
                @if ($pembayaran->catatan_verifikasi)
                    <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800"><span class="font-semibold">Catatan penolakan terakhir:</span> {{ $pembayaran->catatan_verifikasi }}</p>
                @endif
            </section>

            @if ($pembayaran->status === Pembayaran::MENUNGGU_VERIFIKASI)
                <section class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card">
                    <h2 class="text-sm font-semibold text-stone-900">Keputusan</h2>
                    <form method="POST" action="{{ route('admin.pembayaran.verifikasi', $pembayaran) }}" class="mt-3"
                          onsubmit="return confirm('Nyatakan {{ $pembayaran->kode }} lunas? Pesanan akan diteruskan ke petani.')">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
                            <i data-lucide="circle-check" class="h-4 w-4"></i> Nominal sesuai, nyatakan lunas
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.pembayaran.tolak', $pembayaran) }}" class="mt-4 border-t border-stone-100 pt-4">
                        @csrf
                        <label for="alasan" class="block text-sm font-medium text-stone-700">Tolak bukti transfer</label>
                        <textarea id="alasan" name="alasan" rows="3" required minlength="5" maxlength="300"
                                  class="mt-1.5 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                                  placeholder="Mis. nominal kurang Rp 5.000, atau foto buram">{{ old('alasan') }}</textarea>
                        <button type="submit" class="mt-2 w-full rounded-lg px-4 py-2.5 text-sm font-semibold text-red-700 ring-1 ring-inset ring-red-200 hover:bg-red-50">
                            Tolak, minta pembeli unggah ulang
                        </button>
                    </form>
                </section>
            @endif

            <x-dashboard.panel title="Produk dalam tagihan">
                <ul class="-my-1 divide-y divide-stone-100">
                    @foreach ($pembayaran->transaksis as $t)
                        <li class="flex items-center justify-between gap-3 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-stone-900">{{ $t->produk->nama_produk ?? 'Produk' }}</p>
                                <p class="text-xs text-stone-500">{{ $t->jumlah }} {{ $t->produk->satuan ?? '' }} / {{ $t->produk->petani->username ?? '-' }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-sm font-semibold text-stone-900 tabular-nums">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</span>
                                <x-dashboard.status :status="$t->status_transaksi" />
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-dashboard.panel>
        </div>
    </div>
</div>
@endsection
