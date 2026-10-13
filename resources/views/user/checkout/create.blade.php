@extends('layouts.dashboard')
@section('title', 'Checkout')
@section('content')
@use('App\Models\Pembayaran')

@php
    $pilihanMetode = [
        Pembayaran::METODE_TRANSFER => [
            'ikon' => 'landmark',
            'judul' => 'Transfer bank',
            'teks' => 'Transfer ke rekening '.config('marketplace.rekening.bank').', lalu unggah bukti. Admin memverifikasi maksimal 1x24 jam.',
        ],
        Pembayaran::METODE_MIDTRANS => [
            'ikon' => 'credit-card',
            'judul' => 'Midtrans',
            'teks' => 'Virtual account, QRIS, e-wallet, atau kartu. Status pembayaran diperbarui otomatis.',
        ],
    ];
    $metodeDipilih = old('metode', $metodeTersedia[0] ?? null);
    $simulasi = app(\App\Services\Marketplace\MidtransGateway::class)->modeSimulasi();
@endphp

<form method="POST" action="{{ route('user.checkout.store') }}" class="space-y-6">
    @csrf

    <div>
        <a href="{{ route('user.keranjang.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-500 hover:text-stone-900">
            <i data-lucide="arrow-left" class="h-4 w-4"></i> Kembali ke keranjang
        </a>
        <h1 class="mt-3 text-2xl font-bold tracking-tight text-stone-900">Checkout</h1>
    </div>

    <x-flash />

    @error('keranjang')
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Stok berubah sejak produk dimasukkan ke keranjang:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->get('keranjang') as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @enderror

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card sm:p-6">
                <h2 class="text-base font-semibold text-stone-900">Pengiriman</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="alamat_pengiriman" class="block text-sm font-medium text-stone-700">Alamat lengkap</label>
                        <textarea id="alamat_pengiriman" name="alamat_pengiriman" rows="3" required
                                  class="mt-1.5 w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 @error('alamat_pengiriman') border-red-400 @enderror"
                                  placeholder="Jalan, RT/RW, desa, kecamatan, kota/kabupaten">{{ old('alamat_pengiriman', $alamatTerakhir) }}</textarea>
                        @error('alamat_pengiriman')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="catatan" class="block text-sm font-medium text-stone-700">Catatan untuk petani <span class="font-normal text-stone-400">(opsional)</span></label>
                        <input id="catatan" type="text" name="catatan" value="{{ old('catatan') }}" maxlength="300"
                               class="mt-1.5 w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                               placeholder="Mis. kirim pagi hari, pilihkan yang matang">
                        @error('catatan')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card sm:p-6">
                <h2 class="text-base font-semibold text-stone-900">Metode pembayaran</h2>

                @if (empty($metodeTersedia))
                    <p class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Metode pembayaran belum diatur oleh Admin. Silakan coba lagi nanti.
                    </p>
                @else
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($metodeTersedia as $metode)
                            @php $info = $pilihanMetode[$metode]; @endphp
                            <label class="relative flex cursor-pointer gap-3 rounded-xl border border-stone-200 p-4 transition hover:border-stone-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50 has-[:checked]:ring-1 has-[:checked]:ring-brand-500">
                                <input type="radio" name="metode" value="{{ $metode }}" class="sr-only" @checked($metodeDipilih === $metode)>
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-brand-700 ring-1 ring-inset ring-stone-200">
                                    <i data-lucide="{{ $info['ikon'] }}" class="h-[18px] w-[18px]"></i>
                                </span>
                                <span>
                                    <span class="flex items-center gap-2 text-sm font-semibold text-stone-900">
                                        {{ $info['judul'] }}
                                        @if ($metode === Pembayaran::METODE_MIDTRANS && $simulasi)
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800">Simulasi</span>
                                        @endif
                                    </span>
                                    <span class="mt-0.5 block text-sm text-stone-500">{{ $info['teks'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('metode')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                @endif
            </section>
        </div>

        <aside class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card lg:sticky lg:top-24">
            <h2 class="text-base font-semibold text-stone-900">Pesanan Anda</h2>
            <ul class="mt-4 divide-y divide-stone-100">
                @foreach ($items as $item)
                    <li class="flex items-start justify-between gap-3 py-2.5 text-sm">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-stone-900">{{ $item->produk->nama_produk }}</p>
                            <p class="text-stone-500">{{ $item->jumlah }} {{ $item->produk->satuan }} x Rp {{ number_format($item->produk->harga, 0, ',', '.') }}</p>
                        </div>
                        <p class="shrink-0 font-medium text-stone-900 tabular-nums">Rp {{ number_format($item->subtotal(), 0, ',', '.') }}</p>
                    </li>
                @endforeach
            </ul>
            <div class="mt-2 flex justify-between border-t border-stone-200 pt-3 text-base font-semibold text-stone-900">
                <span>Total bayar</span>
                <span class="tabular-nums">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>

            <button type="submit" @disabled(empty($metodeTersedia))
                    class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800 active:translate-y-px disabled:cursor-not-allowed disabled:bg-stone-300">
                <i data-lucide="lock" class="h-4 w-4"></i> Buat pesanan
            </button>
            <p class="mt-3 text-center text-xs leading-relaxed text-stone-500">
                Stok langsung dipesan untuk Anda. Selesaikan pembayaran dalam {{ config('marketplace.batas_bayar_jam') }} jam,
                atau pesanan dibatalkan otomatis.
            </p>
        </aside>
    </div>
</form>
@endsection
