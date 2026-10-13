@extends('layouts.dashboard')

@section('title', 'Profil & Lahan Saya')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">Profil &amp; lahan saya</h1>
            <p class="mt-1 text-sm text-stone-500">Data diri, lahan garapan, dan cuaca terkini di setiap lahan.</p>
        </div>
        <x-dashboard.action :href="route('petani.lahan.create')" icon="plus">Tambah lahan</x-dashboard.action>
    </div>

    <x-flash />

    {{-- Profil petani --}}
    <section class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-stone-900">Profil saya</h2>
            @if ($petaniProfile->isTerverifikasi())
                <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-800 ring-1 ring-inset ring-brand-200/80">
                    <i data-lucide="badge-check" class="h-3.5 w-3.5"></i> Terverifikasi
                </span>
            @else
                <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-200/80">Menunggu verifikasi admin</span>
            @endif
        </div>
        <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-4 text-sm lg:grid-cols-4">
            <div>
                <dt class="text-stone-500">NIK</dt>
                <dd class="mt-0.5 font-medium text-stone-900 tabular-nums">{{ $petaniProfile->nik ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Komoditas utama</dt>
                <dd class="mt-0.5 font-medium text-stone-900">{{ $petaniProfile->komoditas_utama ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Kelompok tani</dt>
                <dd class="mt-0.5 font-medium text-stone-900">{{ $petaniProfile->nama_kelompok_tani ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Total luas lahan</dt>
                <dd class="mt-0.5 font-medium text-stone-900 tabular-nums">{{ rtrim(rtrim(number_format($petaniProfile->luas_lahan, 2, ',', '.'), '0'), ',') }} ha</dd>
            </div>
            @if ($petaniProfile->alamat)
                <div class="col-span-2 lg:col-span-4">
                    <dt class="text-stone-500">Alamat</dt>
                    <dd class="mt-0.5 font-medium text-stone-900">{{ $petaniProfile->alamat }}</dd>
                </div>
            @endif
        </dl>
    </section>

    {{-- Daftar lahan + cuaca --}}
    <section aria-labelledby="judul-lahan" class="space-y-4">
        <h2 id="judul-lahan" class="flex items-center gap-2 text-base font-semibold text-stone-900">
            Lahan saya
            <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs font-semibold text-stone-600 tabular-nums">{{ $lahans->count() }}</span>
        </h2>

        @if ($lahans->isEmpty())
            <div class="rounded-2xl border border-stone-200/80 bg-white p-6 shadow-card">
                <x-dashboard.empty icon="map" title="Belum ada lahan"
                                   description="Tambahkan lahan dan gambar batasnya di peta supaya cuaca di lahan bisa dipantau."
                                   :href="route('petani.lahan.create')" action="Tambah lahan pertama" />
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($lahans as $lahan)
                    @php
                        $status = $lahan->status ?? 'bera';
                        $badge = [
                            'aktif' => 'bg-brand-50 text-brand-800 ring-brand-200/80',
                            'panen' => 'bg-amber-50 text-amber-800 ring-amber-200/80',
                        ][$status] ?? 'bg-stone-100 text-stone-700 ring-stone-200';
                        $cuacaLahan = $cuaca[$lahan->id] ?? null;
                        $titikWilayah = $lahan->latitude === null && $lahan->koordinatCuaca();
                    @endphp
                    <article class="flex flex-col rounded-2xl border border-stone-200/80 bg-white shadow-card">
                        <div class="flex-1 space-y-4 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate text-base font-semibold text-stone-900">{{ $lahan->nama_lahan }}</h3>
                                    <p class="mt-0.5 text-sm text-stone-500">
                                        <span class="tabular-nums">{{ rtrim(rtrim(number_format($lahan->luas_ha, 2, ',', '.'), '0'), ',') }} ha</span>
                                        @if ($lahan->jenis_tanah)
                                            <span class="text-stone-300" aria-hidden="true">/</span> Tanah {{ strtolower($lahan->jenis_tanah) }}
                                        @endif
                                    </p>
                                </div>
                                <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize ring-1 ring-inset {{ $badge }}">{{ $status }}</span>
                            </div>

                            <dl class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt class="text-xs text-stone-500">Tanaman</dt>
                                    <dd class="mt-0.5 font-medium text-stone-900">{{ $lahan->tanaman_aktif ?: '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-stone-500">Perkiraan panen</dt>
                                    <dd class="mt-0.5 font-medium text-stone-900">{{ $lahan->perkiraan_panen?->locale('id')->translatedFormat('j M Y') ?? '-' }}</dd>
                                </div>
                            </dl>

                            @if ($cuacaLahan)
                                <div class="space-y-2">
                                    <x-cuaca.kartu :cuaca="$cuacaLahan" />
                                    @if ($titikWilayah)
                                        <p class="flex items-start gap-1.5 text-xs text-stone-500">
                                            <i data-lucide="map-pin" class="mt-px h-3.5 w-3.5 shrink-0"></i>
                                            Lokasi cuaca mengikuti titik wilayah. Gambar batas lahan agar lebih tepat.
                                        </p>
                                    @endif
                                </div>
                            @elseif ($lahan->koordinatCuaca())
                                <p class="flex items-start gap-2 rounded-xl border border-dashed border-stone-200 bg-stone-50/60 px-4 py-3 text-sm text-stone-500">
                                    <i data-lucide="cloud-off" class="mt-0.5 h-4 w-4 shrink-0"></i>
                                    Cuaca belum bisa diambil. Coba muat ulang halaman beberapa saat lagi.
                                </p>
                            @else
                                <a href="{{ route('petani.lahan.edit', $lahan) }}"
                                   class="flex items-start gap-2 rounded-xl border border-dashed border-stone-200 bg-stone-50/60 px-4 py-3 text-sm text-stone-600 transition hover:border-brand-300 hover:bg-brand-50/40">
                                    <i data-lucide="map-pin-plus" class="mt-0.5 h-4 w-4 shrink-0 text-brand-700"></i>
                                    <span>Gambar batas lahan di peta supaya cuaca di lahan ini bisa ditampilkan.</span>
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center justify-end gap-2 border-t border-stone-100 px-5 py-3">
                            <a href="{{ route('petani.lahan.edit', $lahan) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold text-stone-700 ring-1 ring-inset ring-stone-200 hover:bg-stone-50">
                                <i data-lucide="pencil" class="h-3.5 w-3.5"></i> Ubah
                            </a>
                            <form method="POST" action="{{ route('petani.lahan.destroy', $lahan) }}"
                                  onsubmit="return confirm(@js('Hapus lahan '.$lahan->nama_lahan.'? Riwayat cuacanya ikut terhapus.'))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold text-red-700 ring-1 ring-inset ring-red-200 hover:bg-red-50">
                                    <i data-lucide="trash-2" class="h-3.5 w-3.5"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
