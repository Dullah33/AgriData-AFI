@extends('layouts.dashboard')
@section('title', 'Pembayaran '.$pembayaran->kode)
@section('content')
@use('App\Models\Pembayaran')
@use('App\Models\Transaksi')

@php
    $jumlahRupiah = 'Rp '.number_format($pembayaran->jumlah, 0, ',', '.');
    $batas = $pembayaran->batas_bayar?->copy()->setTimezone('Asia/Jakarta')->locale('id');
    $pengiriman = $pembayaran->transaksis->first();
@endphp

<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('user.pesanan') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-500 hover:text-stone-900">
                <i data-lucide="arrow-left" class="h-4 w-4"></i> Pesanan saya
            </a>
            <h1 class="mt-3 flex flex-wrap items-center gap-3 text-2xl font-bold tracking-tight text-stone-900">
                {{ $pembayaran->kode }}
                <x-marketplace.status-pembayaran :pembayaran="$pembayaran" />
            </h1>
            <p class="mt-1 text-sm text-stone-500">
                Dibuat {{ $pembayaran->created_at->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H:i') }} WIB
                <span class="text-stone-300" aria-hidden="true">/</span> {{ $pembayaran->labelMetode() }}
            </p>
        </div>
    </div>

    <x-flash />

    @if ($galatGateway)
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $galatGateway }}</div>
    @endif

    {{-- ============ STATUS & AKSI PEMBAYARAN ============ --}}
    <section class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card">
        @if ($pembayaran->status === Pembayaran::LUNAS)
            <div class="flex gap-4 p-6">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700"><i data-lucide="circle-check" class="h-6 w-6"></i></span>
                <div>
                    <h2 class="text-base font-semibold text-stone-900">Pembayaran lunas</h2>
                    <p class="mt-1 text-sm text-stone-600">
                        {{ $jumlahRupiah }} diterima {{ $pembayaran->dibayar_at?->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H:i') }} WIB.
                        Pesanan sudah diteruskan ke petani untuk disiapkan dan dikirim.
                    </p>
                </div>
            </div>

        @elseif ($pembayaran->status === Pembayaran::MENUNGGU_VERIFIKASI)
            <div class="flex gap-4 p-6">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700"><i data-lucide="hourglass" class="h-6 w-6"></i></span>
                <div>
                    <h2 class="text-base font-semibold text-stone-900">Bukti transfer sedang diperiksa</h2>
                    <p class="mt-1 text-sm text-stone-600">
                        Dikirim {{ $pembayaran->bukti_diunggah_at?->locale('id')->diffForHumans() }}. Admin biasanya memverifikasi dalam 1x24 jam.
                    </p>
                    <a href="{{ route('user.pembayaran.bukti', $pembayaran) }}" target="_blank" rel="noopener"
                       class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-900">
                        <i data-lucide="file-image" class="h-4 w-4"></i> Lihat bukti yang dikirim
                    </a>
                </div>
            </div>

        @elseif ($pembayaran->bisaDibayar())
            <div class="grid gap-6 p-6 sm:grid-cols-2">
                <div>
                    <p class="text-sm font-medium text-stone-500">Total yang harus dibayar</p>
                    <div class="mt-1 flex items-center gap-2">
                        <p class="text-3xl font-bold tracking-tight text-stone-900 tabular-nums">{{ $jumlahRupiah }}</p>
                        <button type="button" data-salin="{{ (int) round($pembayaran->jumlah) }}" aria-label="Salin nominal"
                                class="rounded-lg p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-700"><i data-lucide="copy" class="h-4 w-4"></i></button>
                    </div>
                    @if ($pembayaran->metode === Pembayaran::METODE_TRANSFER)
                        <p class="mt-1 text-xs text-stone-500">Transfer tepat sesuai nominal agar mudah diverifikasi.</p>
                    @endif
                </div>
                <div class="rounded-xl bg-amber-50 px-4 py-3 ring-1 ring-inset ring-amber-200/70">
                    <p class="text-sm font-medium text-amber-900">Bayar sebelum</p>
                    <p class="mt-0.5 text-sm font-semibold text-amber-900">{{ $batas?->translatedFormat('l, j F Y, H:i') }} WIB</p>
                    <p class="mt-1 text-xs text-amber-800" data-hitung-mundur="{{ $pembayaran->batas_bayar?->toIso8601String() }}"></p>
                </div>
            </div>

            @if ($pembayaran->catatan_verifikasi)
                <div class="mx-6 mb-6 flex gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <i data-lucide="circle-alert" class="mt-0.5 h-4 w-4 shrink-0"></i>
                    <p><span class="font-semibold">Bukti sebelumnya ditolak:</span> {{ $pembayaran->catatan_verifikasi }}</p>
                </div>
            @endif

            @if ($pembayaran->metode === Pembayaran::METODE_TRANSFER)
                <div class="grid gap-6 border-t border-stone-100 bg-stone-50/60 p-6 sm:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-semibold text-stone-900">1. Transfer ke rekening</h3>
                        <dl class="mt-3 space-y-2 rounded-xl bg-white p-4 text-sm ring-1 ring-inset ring-stone-200">
                            <div class="flex justify-between gap-3"><dt class="text-stone-500">Bank</dt><dd class="font-medium text-stone-900">{{ $rekening['bank'] }}</dd></div>
                            <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                                <dt class="text-stone-500">No. rekening</dt>
                                <dd class="ml-auto flex items-center gap-1.5 whitespace-nowrap font-semibold text-stone-900 tabular-nums">
                                    {{ $rekening['nomor'] }}
                                    <button type="button" data-salin="{{ $rekening['nomor'] }}" aria-label="Salin nomor rekening" class="rounded p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-700"><i data-lucide="copy" class="h-3.5 w-3.5"></i></button>
                                </dd>
                            </div>
                            <div class="flex justify-between gap-3"><dt class="shrink-0 text-stone-500">Atas nama</dt><dd class="text-right font-medium text-stone-900">{{ $rekening['atas_nama'] }}</dd></div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-stone-900">2. Unggah bukti transfer</h3>
                        <form method="POST" action="{{ route('user.pembayaran.bukti.store', $pembayaran) }}" enctype="multipart/form-data" class="mt-3">
                            @csrf
                            <label for="bukti_transfer"
                                   class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-stone-300 bg-white px-4 py-6 text-center transition hover:border-brand-400 hover:bg-brand-50/30">
                                <i data-lucide="upload" class="h-6 w-6 text-stone-400"></i>
                                <span class="mt-2 text-sm font-semibold text-stone-700" data-nama-file>Pilih foto atau PDF bukti transfer</span>
                                <span class="mt-0.5 text-xs text-stone-500">JPG, PNG, WEBP, atau PDF, maks. 4 MB</span>
                                <input id="bukti_transfer" type="file" name="bukti_transfer" accept="image/jpeg,image/png,image/webp,application/pdf" required class="sr-only">
                            </label>
                            @error('bukti_transfer')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800 active:translate-y-px">
                                <i data-lucide="send" class="h-4 w-4"></i> Kirim bukti transfer
                            </button>
                        </form>
                    </div>
                </div>

            @elseif ($pembayaran->metode === Pembayaran::METODE_MIDTRANS)
                <div class="border-t border-stone-100 bg-stone-50/60 p-6">
                    @if ($snapToken)
                        <button type="button" id="tombol-bayar"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800 sm:w-auto sm:px-8">
                            <i data-lucide="credit-card" class="h-4 w-4"></i> Bayar sekarang
                        </button>
                        <p class="mt-2 text-xs text-stone-500">Jendela pembayaran Midtrans akan terbuka. Status di halaman ini diperbarui otomatis setelah pembayaran berhasil.</p>
                    @elseif ($midtrans->modeSimulasi())
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="flex items-center gap-2 text-sm font-semibold text-amber-900"><i data-lucide="flask-conical" class="h-4 w-4"></i> Mode simulasi Midtrans</p>
                            <p class="mt-1 text-sm text-amber-800">Server key Midtrans belum diatur. Tombol di bawah menjalankan alur yang sama dengan notifikasi asli dari Midtrans.</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('user.pembayaran.simulasi', $pembayaran) }}">
                                    @csrf
                                    <input type="hidden" name="hasil" value="berhasil">
                                    <button type="submit" class="rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800">Simulasikan pembayaran berhasil</button>
                                </form>
                                <form method="POST" action="{{ route('user.pembayaran.simulasi', $pembayaran) }}">
                                    @csrf
                                    <input type="hidden" name="hasil" value="gagal">
                                    <button type="submit" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-stone-700 ring-1 ring-inset ring-stone-300 hover:bg-stone-50">Simulasikan kedaluwarsa</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-stone-600">Pembayaran Midtrans sedang tidak tersedia. Silakan batalkan pesanan ini lalu checkout ulang dengan transfer bank.</p>
                    @endif
                </div>
            @endif

        @else
            {{-- Dibatalkan, kedaluwarsa, atau gagal --}}
            <div class="flex gap-4 p-6">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-stone-100 text-stone-500"><i data-lucide="circle-x" class="h-6 w-6"></i></span>
                <div>
                    <h2 class="text-base font-semibold text-stone-900">Pesanan {{ strtolower($pembayaran->labelStatus()) }}</h2>
                    <p class="mt-1 text-sm text-stone-600">
                        {{ $pembayaran->transaksis->first()?->alasan_batal ?? 'Pesanan ini sudah ditutup.' }}
                        Stok produk sudah dikembalikan ke petani.
                    </p>
                    <a href="{{ route('user.marketplace') }}" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-900">
                        Belanja lagi <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                </div>
            </div>
        @endif
    </section>

    {{-- ============ RINCIAN PESANAN ============ --}}
    <div class="grid items-start gap-6 lg:grid-cols-3">
        <x-dashboard.panel class="lg:col-span-2" title="Rincian pesanan">
            <ul class="-my-1 divide-y divide-stone-100">
                @foreach ($pembayaran->transaksis as $t)
                    <li class="flex gap-3 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-stone-900">{{ $t->produk->nama_produk ?? 'Produk' }}</p>
                            <p class="text-sm text-stone-500">
                                {{ $t->jumlah }} {{ $t->produk->satuan ?? '' }} x Rp {{ number_format($t->harga_satuan, 0, ',', '.') }}
                                <span class="text-stone-300" aria-hidden="true">/</span> {{ $t->produk->petani->username ?? '-' }}
                            </p>
                            @if ($t->status_transaksi === Transaksi::BATAL && $t->alasan_batal)
                                <p class="mt-1 text-xs text-red-600">{{ $t->alasan_batal }}</p>
                            @endif
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <p @class(['text-sm font-semibold tabular-nums', 'text-stone-400 line-through' => $t->status_transaksi === Transaksi::BATAL, 'text-stone-900' => $t->status_transaksi !== Transaksi::BATAL])>
                                Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                            </p>
                            <x-dashboard.status :status="$t->status_transaksi" />
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="mt-3 flex justify-between border-t border-stone-200 pt-3 text-sm font-semibold text-stone-900">
                <span>Total</span>
                <span class="tabular-nums">{{ $jumlahRupiah }}</span>
            </div>
        </x-dashboard.panel>

        <div class="space-y-4">
            <x-dashboard.panel title="Pengiriman">
                <p class="whitespace-pre-line text-sm text-stone-700">{{ $pengiriman?->alamat_pengiriman ?? '-' }}</p>
                @if ($pengiriman?->catatan)
                    <p class="mt-3 text-sm text-stone-500"><span class="font-medium text-stone-700">Catatan:</span> {{ $pengiriman->catatan }}</p>
                @endif
            </x-dashboard.panel>

            @if ($pembayaran->bisaDibatalkanPembeli())
                <form method="POST" action="{{ route('user.pembayaran.batal', $pembayaran) }}"
                      onsubmit="return confirm('Batalkan pesanan {{ $pembayaran->kode }}? Stok akan dikembalikan ke petani.')">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold text-red-700 ring-1 ring-inset ring-red-200 transition hover:bg-red-50">
                        <i data-lucide="x" class="h-4 w-4"></i> Batalkan pesanan
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    @if ($snapToken)
        <script src="{{ $midtrans->snapJsUrl() }}" data-client-key="{{ $midtrans->clientKey() }}"></script>
        <script>
            document.getElementById('tombol-bayar').addEventListener('click', () => {
                // Status final tetap ditentukan notifikasi server (webhook), bukan callback ini
                window.snap.pay(@js($snapToken), {
                    onSuccess: () => window.location.reload(),
                    onPending: () => window.location.reload(),
                    onError: () => window.location.reload(),
                });
            });
        </script>
    @endif
    <script>
        // Salin nomor rekening / nominal
        document.querySelectorAll('[data-salin]').forEach((tombol) => {
            tombol.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(tombol.dataset.salin);
                    tombol.setAttribute('aria-label', 'Tersalin');
                    tombol.classList.add('text-brand-600');
                    setTimeout(() => tombol.classList.remove('text-brand-600'), 1200);
                } catch (e) {}
            });
        });

        // Nama file bukti yang dipilih
        const inputBukti = document.getElementById('bukti_transfer');
        inputBukti?.addEventListener('change', () => {
            const label = document.querySelector('[data-nama-file]');
            if (inputBukti.files[0]) label.textContent = inputBukti.files[0].name;
        });

        // Hitung mundur batas bayar
        document.querySelectorAll('[data-hitung-mundur]').forEach((el) => {
            const batas = new Date(el.dataset.hitungMundur).getTime();
            const perbarui = () => {
                const sisa = Math.max(0, batas - Date.now());
                const jam = Math.floor(sisa / 3600000);
                const menit = Math.floor((sisa % 3600000) / 60000);
                el.textContent = sisa > 0 ? `Sisa waktu ${jam} jam ${menit} menit` : 'Batas waktu sudah lewat. Muat ulang halaman.';
            };
            perbarui();
            setInterval(perbarui, 30000);
        });
    </script>
@endpush
@endsection
