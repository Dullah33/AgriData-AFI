@extends('layouts.dashboard')
@section('title', 'Verifikasi Pembayaran')
@section('content')
@use('App\Models\Pembayaran')

@php
    $tab = [
        Pembayaran::MENUNGGU_VERIFIKASI => 'Perlu diverifikasi',
        Pembayaran::MENUNGGU_PEMBAYARAN => 'Belum dibayar',
        Pembayaran::LUNAS => 'Lunas',
        Pembayaran::DIBATALKAN => 'Dibatalkan',
        Pembayaran::KEDALUWARSA => 'Kedaluwarsa',
        Pembayaran::GAGAL => 'Gagal',
        'semua' => 'Semua',
    ];
@endphp

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-stone-900">Verifikasi pembayaran</h1>
        <p class="mt-1 text-sm text-stone-500">Cocokkan bukti transfer dengan nominal tagihan sebelum menyatakan lunas.</p>
    </div>

    <x-flash />

    <nav class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Filter status">
        <ul class="flex gap-1 border-b border-stone-200">
            @foreach ($tab as $nilai => $label)
                @php
                    $aktif = $status === $nilai;
                    $jumlah = $nilai === 'semua' ? $jumlahPerStatus->sum() : ($jumlahPerStatus[$nilai] ?? 0);
                @endphp
                <li>
                    <a href="{{ route('admin.pembayaran.index', ['status' => $nilai]) }}" @if ($aktif) aria-current="page" @endif
                       @class([
                           '-mb-px flex items-center gap-2 whitespace-nowrap border-b-2 px-3 py-2.5 text-sm font-medium transition-colors',
                           'border-brand-600 text-brand-800' => $aktif,
                           'border-transparent text-stone-500 hover:text-stone-800' => ! $aktif,
                       ])>
                        {{ $label }}
                        <span @class(['rounded-full px-1.5 py-0.5 text-xs font-semibold tabular-nums',
                            'bg-amber-100 text-amber-800' => $nilai === Pembayaran::MENUNGGU_VERIFIKASI && $jumlah > 0,
                            'bg-stone-100 text-stone-600' => ! ($nilai === Pembayaran::MENUNGGU_VERIFIKASI && $jumlah > 0)])>{{ $jumlah }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card">
        @if ($pembayarans->isEmpty())
            <div class="p-6">
                <x-dashboard.empty icon="wallet" title="Tidak ada pembayaran" description="Pembayaran dengan status ini akan muncul di sini." />
            </div>
        @else
            <ul class="divide-y divide-stone-100">
                @foreach ($pembayarans as $p)
                    <li>
                        <a href="{{ route('admin.pembayaran.show', $p) }}" class="flex flex-wrap items-center gap-4 px-5 py-4 transition-colors hover:bg-stone-50">
                            <x-dashboard.avatar :name="$p->pembeli->username" />
                            <div class="min-w-0 flex-1">
                                <p class="flex flex-wrap items-center gap-2 text-sm font-semibold text-stone-900">
                                    {{ $p->kode }} <x-marketplace.status-pembayaran :pembayaran="$p" />
                                </p>
                                <p class="mt-0.5 text-sm text-stone-500">
                                    {{ $p->pembeli->username }} / {{ $p->labelMetode() }} / {{ $p->transaksis->count() }} produk
                                    @if ($p->bukti_diunggah_at && $p->status === Pembayaran::MENUNGGU_VERIFIKASI)
                                        / bukti dikirim {{ $p->bukti_diunggah_at->locale('id')->diffForHumans() }}
                                    @endif
                                </p>
                            </div>
                            <p class="text-sm font-semibold text-stone-900 tabular-nums">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</p>
                            <i data-lucide="chevron-right" class="h-4 w-4 text-stone-300"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if ($pembayarans->hasPages())
        <div>{{ $pembayarans->links() }}</div>
    @endif
</div>
@endsection
