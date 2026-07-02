@php
    // Konfigurasi sidebar per role, mengikuti BAB 3A dokumen modul sistem.
    // 'route' diisi null untuk halaman yang rute/view-nya belum dibuat —
    // tinggal isi route()-nya nanti begitu halaman terkait sudah jadi.
    $sidebarConfig = [
        'admin' => [
            'label' => 'ADMIN',
            'color' => '#1A3C8A', // Warna seragam untuk semua role
            'menu' => [
                ['label' => 'Dashboard', 'route' => 'dashboard'],
                ['label' => 'Manajemen Petani', 'route' => 'admin.petani.index'],
                ['label' => 'Manajemen Penyuluh', 'route' => null],
                ['label' => 'Peta Lahan & Wilayah', 'route' => null],
                ['label' => 'Pemetaan Penyakit', 'route' => null],
                ['label' => 'Artikel Pertanian', 'route' => 'admin.artikel.index'],
                ['label' => 'Moderasi Ulasan', 'route' => 'admin.ulasan.index'],
                ['label' => 'Laporan & Ekspor Data', 'route' => null],
                ['label' => 'Pengaturan Akun', 'route' => null],
            ],
        ],
        'petani' => [
            'label' => 'PETANI',
            'color' => '#1A3C8A', // Disamakan dengan warna Admin
            'menu' => [
                ['label' => 'Dashboard', 'route' => 'dashboard'],
                ['label' => 'Profil & Lahan Saya', 'route' => 'petani.lahan.index'],
                ['label' => 'Listing Hasil Panen', 'route' => 'petani.produk.index'],
                ['label' => 'Pesanan Masuk', 'route' => 'petani.pesanan.index'],
                ['label' => 'Analisis Cuaca', 'route' => null],
                ['label' => 'AI Scanner Penyakit', 'route' => null],
                ['label' => 'Kunjungan Penyuluh', 'route' => null],
                ['label' => 'Artikel Pertanian', 'route' => null],
                ['label' => 'Pengaturan Akun', 'route' => null],
            ],
        ],
        'user' => [
            'label' => 'USER',
            'color' => '#1A3C8A', // Disamakan dengan warna Admin
            'menu' => [
                ['label' => 'Dashboard', 'route' => 'dashboard'],
                ['label' => 'Marketplace Hasil Panen', 'route' => 'user.marketplace'],
                ['label' => 'Keranjang & Pesanan Saya', 'route' => 'user.pesanan'],
                ['label' => 'AI Scanner (Terbatas)', 'route' => null],
                ['label' => 'Analisis Cuaca', 'route' => null],
                ['label' => 'Profil Petani (Lihat)', 'route' => null],
                ['label' => 'Artikel Pertanian', 'route' => null],
                ['label' => 'Riwayat & Ulasan Saya', 'route' => null],
                ['label' => 'Pengaturan Akun', 'route' => null],
            ],
        ],
        'penyuluh' => [
            'label' => 'PETUGAS PENYULUH',
            'color' => '#1A3C8A', // Disamakan dengan warna Admin
            'menu' => [
                ['label' => 'Dashboard', 'route' => 'dashboard'],
                ['label' => 'Wilayah Binaan', 'route' => null],
                ['label' => 'Jadwal Kunjungan', 'route' => null],
                ['label' => 'Laporan Kunjungan', 'route' => null],
                ['label' => 'Deteksi Penyakit (Wilayah)', 'route' => null],
                ['label' => 'Pelatihan Kelompok Tani', 'route' => null],
                ['label' => 'Analisis Cuaca (Wilayah)', 'route' => null],
                ['label' => 'Laporan Bulanan', 'route' => null],
                ['label' => 'Pengaturan Akun', 'route' => null],
            ],
        ],
    ];

    $role = Auth::user()->role;
    $config = $sidebarConfig[$role] ?? $sidebarConfig['user'];
@endphp

<aside class="w-64 shrink-0 text-white" style="background-color: {{ $config['color'] }};">
    <div class="px-5 py-4 border-b border-white/20 flex items-center gap-2">
        <span class="text-lg font-bold">Agri Data</span>
    </div>

    <div class="px-5 pt-4 pb-2">
        <span class="inline-block text-xs font-semibold bg-white/20 rounded-full px-3 py-1">
            {{ $config['label'] }}
        </span>
    </div>

    <nav class="px-3 pb-6">
        <p class="px-2 pb-2 text-xs uppercase tracking-wide text-white/60">Menu Utama</p>
        <ul class="space-y-1">
            @foreach ($config['menu'] as $item)
                <li>
                    @if ($item['route'] && \Illuminate\Support\Facades\Route::has($item['route']))
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm
                                  {{ request()->routeIs($item['route']) ? 'bg-white/20 font-semibold' : 'hover:bg-white/10 text-white/90' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/70"></span>
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-white/40 cursor-not-allowed">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/30"></span>
                            {{ $item['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>
</aside>