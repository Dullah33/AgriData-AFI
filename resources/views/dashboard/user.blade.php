@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('content')

<div class="space-y-8">
    <x-dashboard.page-header subtitle="Belanja hasil panen langsung dari petani dan pantau pesanan Anda.">
        <x-slot:actions>
            <x-dashboard.action :href="route('user.deteksi-penyakit.create')" icon="scan-search" variant="secondary">Scan tanaman</x-dashboard.action>
            <x-dashboard.action :href="route('user.marketplace')" icon="store">Buka marketplace</x-dashboard.action>
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
        <x-dashboard.stat label="Pesanan aktif" :value="$stats['pesanan_aktif']" icon="shopping-basket" :href="route('user.pesanan')"
                          hint="Menunggu & sedang diproses" />
        <x-dashboard.stat label="Pesanan selesai" :value="$stats['pesanan_selesai']" icon="package" :href="route('user.pesanan')"
                          hint="Total transaksi berhasil" />
        <x-dashboard.stat class="col-span-2 sm:col-span-1" label="Belum diulas" :value="$stats['belum_diulas']" icon="star" :href="route('user.riwayat-ulasan')"
                          tone="attention" :hint="$stats['belum_diulas'] > 0 ? 'Bantu petani dengan ulasan' : 'Semua sudah diulas'" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-dashboard.panel title="Pesanan terbaru" :href="route('user.pesanan')">
            @if ($pesananTerbaru->isEmpty())
                <x-dashboard.empty icon="shopping-basket" title="Belum ada pesanan"
                                   description="Hasil panen segar dari petani lokal menunggu di marketplace."
                                   :href="route('user.marketplace')" action="Mulai belanja" />
            @else
                <ul class="-my-1 divide-y divide-stone-100">
                    @foreach ($pesananTerbaru as $p)
                        <li class="flex items-center gap-3 py-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-stone-100 text-stone-500">
                                <i data-lucide="package" class="h-5 w-5"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-stone-900">{{ $p->produk->nama_produk }}</p>
                                <p class="text-sm text-stone-500">{{ $p->created_at->locale('id')->translatedFormat('j M Y') }}</p>
                            </div>
                            <x-dashboard.status :status="$p->status_transaksi" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dashboard.panel>

        <x-dashboard.panel title="Baru di marketplace" :href="route('user.marketplace')">
            @if ($produkTerbaru->isEmpty())
                <x-dashboard.empty icon="store" title="Belum ada produk tersedia"
                                   description="Produk dari petani akan tampil di sini begitu didaftarkan." />
            @else
                <ul class="-my-1 divide-y divide-stone-100">
                    @foreach ($produkTerbaru as $p)
                        <li>
                            <a href="{{ route('user.marketplace.show', $p) }}"
                               class="-mx-2 flex items-center gap-3 rounded-lg px-2 py-3 transition-colors hover:bg-stone-50">
                                @if ($p->foto_produk)
                                    <img src="{{ Storage::url($p->foto_produk) }}" alt="" loading="lazy"
                                         class="h-11 w-11 shrink-0 rounded-lg bg-stone-100 object-cover">
                                @else
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                        <i data-lucide="wheat" class="h-5 w-5"></i>
                                    </span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-stone-900">{{ $p->nama_produk }}</p>
                                    <p class="truncate text-sm text-stone-500">{{ $p->petani->username }}</p>
                                </div>
                                <p class="shrink-0 text-right">
                                    <span class="block text-sm font-semibold text-stone-900 tabular-nums">Rp {{ number_format($p->harga, 0, ',', '.') }}</span>
                                    <span class="block text-xs text-stone-500">per {{ $p->satuan }}</span>
                                </p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dashboard.panel>
    </div>
</div>
@endsection
