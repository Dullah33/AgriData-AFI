@php
    // Konfigurasi sidebar per role, mengikuti BAB 3A dokumen modul sistem.
    // - 'route'  : nama route tujuan. Item dengan route yang belum ada
    //              ditampilkan nonaktif (label "Segera").
    // - 'active' : pola route yang membuat item ini menyala. Default sama
    //              dengan 'route'. Pakai wildcard supaya halaman turunan
    //              (detail, edit) tetap menandai menu induknya.
    // - 'icon'   : nama ikon Lucide (https://lucide.dev/icons).
    // - 'badge'  : angka kecil di sisi kanan (mis. isi keranjang), opsional.
    // "Pengaturan Akun" dipindah ke bagian bawah sidebar dan menu profil.
    // $jumlahKeranjang & $jumlahVerifikasi dari App\View\Composers\NavigasiComposer
    $peran = Auth::user()->role;

    $sidebarConfig = [
        'admin' => [
            'label' => 'Admin',
            'groups' => [
                ['title' => null, 'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard'],
                ]],
                ['title' => 'Pengguna', 'items' => [
                    ['label' => 'Manajemen Petani', 'route' => 'admin.petani.index', 'active' => 'admin.petani.*', 'icon' => 'users'],
                    ['label' => 'Manajemen Penyuluh', 'route' => 'admin.penyuluh.index', 'active' => 'admin.penyuluh.*', 'icon' => 'user-check'],
                ]],
                ['title' => 'Transaksi', 'items' => [
                    ['label' => 'Verifikasi Pembayaran', 'route' => 'admin.pembayaran.index', 'active' => 'admin.pembayaran.*', 'icon' => 'wallet', 'badge' => $jumlahVerifikasi],
                ]],
                ['title' => 'Data & konten', 'items' => [
                    ['label' => 'Manajemen Tanaman', 'route' => 'admin.plants.index', 'active' => 'admin.plants.*', 'icon' => 'sprout'],
                    ['label' => 'Peta Lahan, Wilayah & Penyakit', 'route' => 'admin.peta-lahan.index', 'active' => 'admin.peta-lahan.*', 'icon' => 'map'],
                    ['label' => 'Artikel Pertanian', 'route' => 'admin.artikel.index', 'active' => 'admin.artikel.*', 'icon' => 'newspaper'],
                    ['label' => 'Moderasi Ulasan', 'route' => 'admin.ulasan.index', 'active' => 'admin.ulasan.*', 'icon' => 'message-square'],
                ]],
                ['title' => 'Laporan', 'items' => [
                    ['label' => 'Laporan & Ekspor Data', 'route' => 'admin.laporan.index', 'active' => 'admin.laporan.*', 'icon' => 'file-text'],
                ]],
            ],
        ],
        'petani' => [
            'label' => 'Petani',
            'groups' => [
                ['title' => null, 'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard'],
                ]],
                ['title' => 'Lahan & penjualan', 'items' => [
                    ['label' => 'Profil & Lahan Saya', 'route' => 'petani.lahan.index', 'active' => 'petani.lahan.*', 'icon' => 'map'],
                    ['label' => 'Listing Hasil Panen', 'route' => 'petani.produk.index', 'active' => 'petani.produk.*', 'icon' => 'package'],
                    ['label' => 'Pesanan Masuk', 'route' => 'petani.pesanan.index', 'active' => 'petani.pesanan.*', 'icon' => 'inbox'],
                ]],
                ['title' => 'Kesehatan tanaman', 'items' => [
                    ['label' => 'AI Scanner Penyakit', 'route' => 'petani.deteksi-penyakit.create', 'icon' => 'scan-search'],
                    ['label' => 'Riwayat Deteksi Penyakit', 'route' => 'petani.deteksi-penyakit.index', 'active' => ['petani.deteksi-penyakit.index', 'petani.deteksi-penyakit.show'], 'icon' => 'history'],
                    ['label' => 'Panduan Budidaya', 'route' => 'budidaya.index', 'active' => 'budidaya.*', 'icon' => 'book-open'],
                    ['label' => 'Analisis Cuaca', 'route' => 'cuaca.index', 'active' => 'cuaca.*', 'icon' => 'cloud-sun'],
                ]],
                ['title' => 'Pendampingan', 'items' => [
                    ['label' => 'Kunjungan Penyuluh', 'route' => 'petani.kunjungan-penyuluh.index', 'active' => 'petani.kunjungan-penyuluh.*', 'icon' => 'calendar-check'],
                    ['label' => 'Artikel Pertanian', 'route' => 'artikel.index', 'active' => 'artikel.*', 'icon' => 'newspaper'],
                ]],
            ],
        ],
        'user' => [
            'label' => 'Pengguna',
            'groups' => [
                ['title' => null, 'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard'],
                ]],
                ['title' => 'Belanja', 'items' => [
                    ['label' => 'Marketplace Hasil Panen', 'route' => 'user.marketplace', 'active' => ['user.marketplace', 'user.marketplace.*'], 'icon' => 'store'],
                    ['label' => 'Keranjang', 'route' => 'user.keranjang.index', 'active' => ['user.keranjang.*', 'user.checkout.*'], 'icon' => 'shopping-cart', 'badge' => $jumlahKeranjang],
                    ['label' => 'Pesanan Saya', 'route' => 'user.pesanan', 'active' => ['user.pesanan', 'user.pembayaran.*'], 'icon' => 'shopping-basket'],
                    ['label' => 'Riwayat & Ulasan Saya', 'route' => 'user.riwayat-ulasan', 'active' => ['user.riwayat-ulasan', 'user.ulasan.*'], 'icon' => 'star'],
                    ['label' => 'Profil Petani (Lihat)', 'route' => 'user.petani.index', 'active' => 'user.petani.*', 'icon' => 'users'],
                ]],
                ['title' => 'Belajar', 'items' => [
                    ['label' => 'AI Scanner (Terbatas)', 'route' => 'user.deteksi-penyakit.create', 'active' => 'user.deteksi-penyakit.*', 'icon' => 'scan-search'],
                    ['label' => 'Analisis Cuaca', 'route' => 'cuaca.index', 'active' => 'cuaca.*', 'icon' => 'cloud-sun'],
                    ['label' => 'Panduan Budidaya', 'route' => 'budidaya.index', 'active' => 'budidaya.*', 'icon' => 'book-open'],
                    ['label' => 'Artikel Pertanian', 'route' => 'artikel.index', 'active' => 'artikel.*', 'icon' => 'newspaper'],
                ]],
            ],
        ],
        'penyuluh' => [
            'label' => 'Petugas Penyuluh',
            'groups' => [
                ['title' => null, 'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard'],
                ]],
                ['title' => 'Wilayah', 'items' => [
                    ['label' => 'Wilayah Binaan', 'route' => 'penyuluh.wilayah-binaan.index', 'active' => 'penyuluh.wilayah-binaan.*', 'icon' => 'map-pin'],
                    ['label' => 'Deteksi Penyakit (Wilayah)', 'route' => 'penyuluh.deteksi-penyakit.index', 'active' => 'penyuluh.deteksi-penyakit.*', 'icon' => 'scan-search'],
                    ['label' => 'Analisis Cuaca (Wilayah)', 'route' => 'cuaca.index', 'active' => 'cuaca.*', 'icon' => 'cloud-sun'],
                ]],
                ['title' => 'Kegiatan', 'items' => [
                    ['label' => 'Jadwal Kunjungan', 'route' => 'penyuluh.kunjungan.index', 'active' => ['penyuluh.kunjungan.index', 'penyuluh.kunjungan.create', 'penyuluh.kunjungan.laporkan'], 'icon' => 'calendar-days'],
                    ['label' => 'Laporan Kunjungan', 'route' => 'penyuluh.kunjungan.riwayat', 'icon' => 'clipboard-list'],
                    ['label' => 'Pelatihan Kelompok Tani', 'route' => 'penyuluh.pelatihan.index', 'active' => 'penyuluh.pelatihan.*', 'icon' => 'graduation-cap'],
                    ['label' => 'Laporan Bulanan', 'route' => 'penyuluh.laporan-bulanan.index', 'active' => 'penyuluh.laporan-bulanan.*', 'icon' => 'file-text'],
                ]],
                ['title' => 'Referensi', 'items' => [
                    ['label' => 'Panduan Budidaya', 'route' => 'budidaya.index', 'active' => 'budidaya.*', 'icon' => 'book-open'],
                ]],
            ],
        ],
    ];

    $user = Auth::user();
    $config = $sidebarConfig[$user->role] ?? $sidebarConfig['user'];
    $inisial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->username, 0, 2));
@endphp

{{-- Latar gelap di belakang sidebar saat dibuka di HP --}}
<div id="sidebar-backdrop" data-sidebar-close aria-hidden="true"
     class="fixed inset-0 z-40 bg-brand-950/40 backdrop-blur-[2px] opacity-0 pointer-events-none transition-opacity duration-300 lg:hidden"></div>

<aside id="sidebar" aria-label="Navigasi utama"
       class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] flex-col bg-brand-950 text-stone-300
              -translate-x-full transition-transform duration-300 ease-[cubic-bezier(.16,1,.3,1)]
              motion-reduce:transition-none lg:translate-x-0">

    {{-- Logo --}}
    <div class="flex h-16 shrink-0 items-center justify-between px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-400">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500/20 text-brand-300 ring-1 ring-inset ring-brand-400/20">
                <i data-lucide="sprout" class="h-[18px] w-[18px]"></i>
            </span>
            <span class="text-[15px] font-bold tracking-tight text-white">AgriScan</span>
        </a>
        <button type="button" data-sidebar-close aria-label="Tutup menu"
                class="-mr-2 inline-flex h-9 w-9 items-center justify-center rounded-lg text-stone-400 hover:bg-white/5 hover:text-white lg:hidden">
            <i data-lucide="x" class="h-5 w-5"></i>
        </button>
    </div>

    <div class="px-5 pb-4">
        <span class="inline-flex items-center rounded-full bg-white/5 px-2.5 py-1 text-xs font-medium text-brand-200 ring-1 ring-inset ring-white/10">
            Panel {{ $config['label'] }}
        </span>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 overflow-y-auto overscroll-contain px-3 pb-6">
        @foreach ($config['groups'] as $group)
            <div @class(['mt-6' => ! $loop->first])>
                @if ($group['title'])
                    <p class="px-3 pb-2 text-xs font-medium text-stone-500">{{ $group['title'] }}</p>
                @endif
                <ul class="space-y-0.5">
                    @foreach ($group['items'] as $item)
                        @php
                            $tersedia = \Illuminate\Support\Facades\Route::has($item['route']);
                            $aktif = $tersedia && request()->routeIs(...(array) ($item['active'] ?? $item['route']));
                        @endphp
                        <li>
                            @if ($tersedia)
                                <a href="{{ route($item['route']) }}" @if ($aktif) aria-current="page" @endif
                                   @class([
                                       'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                       'focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-400',
                                       'bg-white/10 text-white' => $aktif,
                                       'text-stone-400 hover:bg-white/5 hover:text-stone-100' => ! $aktif,
                                   ])>
                                    <i data-lucide="{{ $item['icon'] }}"
                                       @class(['h-[18px] w-[18px] shrink-0', 'text-brand-300' => $aktif, 'text-stone-500 group-hover:text-stone-300' => ! $aktif])></i>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                    @if (! empty($item['badge']))
                                        <span class="ml-auto rounded-full bg-brand-500/25 px-2 py-0.5 text-xs font-semibold text-brand-100 tabular-nums">{{ $item['badge'] }}</span>
                                    @endif
                                </a>
                            @else
                                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-stone-600">
                                    <i data-lucide="{{ $item['icon'] }}" class="h-[18px] w-[18px] shrink-0"></i>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                    <span class="ml-auto text-[11px] font-medium text-stone-600">Segera</span>
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    {{-- Akun --}}
    <div class="shrink-0 border-t border-white/10 p-3">
        <div class="flex items-center gap-3 rounded-lg px-2 py-2">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-700 text-xs font-semibold text-white">{{ $inisial }}</span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-white">{{ $user->username }}</p>
                <p class="truncate text-xs text-stone-500">{{ $user->email }}</p>
            </div>
            <a href="{{ route('akun.edit') }}" title="Pengaturan akun" aria-label="Pengaturan akun"
               @class([
                   'inline-flex h-8 w-8 items-center justify-center rounded-lg transition-colors hover:bg-white/5 hover:text-white',
                   'text-brand-300' => request()->routeIs('akun.*'),
                   'text-stone-400' => ! request()->routeIs('akun.*'),
               ])>
                <i data-lucide="settings" class="h-[18px] w-[18px]"></i>
            </a>
        </div>
    </div>
</aside>
