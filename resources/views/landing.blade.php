<!DOCTYPE html>
<html lang="id" class="scroll-smooth bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f2219">
    <meta name="description" content="AgriScan membantu petani mengenali penyakit tanaman dari foto, memantau cuaca, dan menjual hasil panen, didampingi penyuluh setempat.">
    <title>AgriScan - Tanaman sehat, panen lebih pasti</title>

    <script>document.documentElement.classList.add('js')</script>
    @include('partials.head-assets')

    <link rel="preload" as="image" href="{{ asset('assets/images/landing/petani-tablet.jpg') }}" fetchpriority="high">

    <style>
        /* Elemen [data-reveal] muncul halus saat masuk layar. Tanpa JS atau
           saat pengguna memilih kurangi animasi, elemen langsung tampil. */
        .js [data-reveal] {
            opacity: 0;
            transform: translateY(16px);
            transition: opacity .7s cubic-bezier(.16, 1, .3, 1), transform .7s cubic-bezier(.16, 1, .3, 1);
            transition-delay: var(--delay, 0ms);
        }
        .js [data-reveal].terlihat {
            opacity: 1;
            transform: none;
        }
        @media (prefers-reduced-motion: reduce) {
            .js [data-reveal] {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }
    </style>
</head>
<body class="font-sans text-stone-900 antialiased">

    {{-- ============ NAVIGASI ============ --}}
    <header id="atas" class="sticky top-0 z-40 border-b border-transparent bg-white/80 backdrop-blur-md transition-colors duration-300 supports-[backdrop-filter]:bg-white/70">
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
            <a href="{{ route('landing') }}" class="flex items-center gap-2.5 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-800 text-brand-100">
                    <i data-lucide="sprout" class="h-[18px] w-[18px]"></i>
                </span>
                <span class="text-[15px] font-bold tracking-tight">AgriScan</span>
            </a>

            <ul class="hidden items-center gap-1 text-sm font-medium text-stone-600 lg:flex">
                <li><a href="#features" class="rounded-lg px-3 py-2 transition-colors hover:bg-stone-100 hover:text-stone-900">Fitur</a></li>
                <li><a href="#cara-kerja" class="rounded-lg px-3 py-2 transition-colors hover:bg-stone-100 hover:text-stone-900">Cara kerja</a></li>
                <li><a href="#about" class="rounded-lg px-3 py-2 transition-colors hover:bg-stone-100 hover:text-stone-900">Tentang</a></li>
                <li><a href="#contact" class="rounded-lg px-3 py-2 transition-colors hover:bg-stone-100 hover:text-stone-900">Kontak</a></li>
            </ul>

            <div class="hidden items-center gap-2 lg:flex">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-900 active:translate-y-px">
                        Buka dashboard <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-stone-700 transition-colors hover:bg-stone-100 hover:text-stone-900">Masuk</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-900 active:translate-y-px">Daftar gratis</a>
                @endauth
            </div>

            <button type="button" id="tombol-menu" aria-controls="menu-hp" aria-expanded="false" aria-label="Buka menu"
                    class="-mr-2 inline-flex h-10 w-10 items-center justify-center rounded-lg text-stone-700 hover:bg-stone-100 lg:hidden">
                <i data-lucide="menu" class="h-5 w-5" data-ikon-buka></i>
                <i data-lucide="x" class="hidden h-5 w-5" data-ikon-tutup></i>
            </button>
        </nav>

        {{-- Menu HP: turun dengan transisi transform + opacity --}}
        <div id="menu-hp"
             class="pointer-events-none absolute inset-x-0 top-full -translate-y-2 border-b border-stone-200 bg-white px-4 pb-6 pt-2 opacity-0 shadow-lift transition duration-200 ease-out motion-reduce:transition-none sm:px-6 lg:hidden">
            <ul class="space-y-1 text-base font-medium text-stone-700">
                <li><a href="#features" data-tutup-menu class="block rounded-lg px-3 py-2.5 hover:bg-stone-100">Fitur</a></li>
                <li><a href="#cara-kerja" data-tutup-menu class="block rounded-lg px-3 py-2.5 hover:bg-stone-100">Cara kerja</a></li>
                <li><a href="#about" data-tutup-menu class="block rounded-lg px-3 py-2.5 hover:bg-stone-100">Tentang</a></li>
                <li><a href="#contact" data-tutup-menu class="block rounded-lg px-3 py-2.5 hover:bg-stone-100">Kontak</a></li>
            </ul>
            <div class="mt-4 grid gap-2 border-t border-stone-100 pt-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-brand-800 px-4 py-2.5 text-center text-sm font-semibold text-white">Buka dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="rounded-lg bg-brand-800 px-4 py-2.5 text-center text-sm font-semibold text-white">Daftar gratis</a>
                    <a href="{{ route('login') }}" class="rounded-lg px-4 py-2.5 text-center text-sm font-semibold text-stone-700 ring-1 ring-inset ring-stone-200">Masuk</a>
                @endauth
            </div>
        </div>
    </header>
    <div id="penanda-atas" class="absolute top-0 h-px w-px" aria-hidden="true"></div>

    <main>
        {{-- ============ HERO ============ --}}
        <section class="relative overflow-hidden bg-gradient-to-b from-brand-50/70 via-white to-white">
            <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 pb-16 pt-12 sm:px-6 sm:pt-16 lg:grid-cols-12 lg:gap-8 lg:px-8 lg:pb-16 lg:pt-20">
                <div class="lg:col-span-6 xl:col-span-5">
                    <p class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-sm font-medium text-brand-800 ring-1 ring-inset ring-brand-200 motion-safe:animate-fade-up">
                        <i data-lucide="leaf" class="h-4 w-4 text-brand-600"></i>
                        Untuk petani, penyuluh, dan pembeli
                    </p>
                    <h1 class="mt-6 text-4xl font-bold leading-[1.08] tracking-[-0.02em] text-stone-900 sm:text-5xl lg:text-[56px] motion-safe:animate-fade-up [animation-delay:80ms]">
                        Tanaman sehat,<br class="hidden sm:block"> panen lebih pasti.
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-stone-600 motion-safe:animate-fade-up [animation-delay:160ms]">
                        Foto daun yang sakit, AgriScan memberi diagnosis dan cara penanganannya dalam hitungan detik,
                        lalu menghubungkan Anda dengan penyuluh setempat.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row motion-safe:animate-fade-up [animation-delay:240ms]">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-800 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-brand-900 active:translate-y-px">
                                Buka dashboard <i data-lucide="arrow-right" class="h-5 w-5"></i>
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-800 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-brand-900 active:translate-y-px">
                                Daftar gratis <i data-lucide="arrow-right" class="h-5 w-5"></i>
                            </a>
                        @endauth
                        <a href="#features" class="inline-flex items-center justify-center rounded-lg bg-white px-6 py-3 text-base font-semibold text-stone-800 ring-1 ring-inset ring-stone-200 transition hover:bg-stone-50 active:translate-y-px">
                            Lihat fitur
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-6 xl:col-span-7 motion-safe:animate-fade-up [animation-delay:200ms]">
                    <div class="relative mx-auto max-w-xl lg:ml-auto lg:mr-0 xl:max-w-2xl">
                        <img src="{{ asset('assets/images/landing/petani-tablet.jpg') }}" width="960" height="960"
                             alt="Petani memakai tablet di tengah sawah terasering"
                             fetchpriority="high" decoding="async"
                             class="aspect-[4/3] w-full rounded-3xl object-cover object-[50%_40%] shadow-lift ring-1 ring-stone-900/5 sm:aspect-[5/4]">
                    </div>
                </div>
            </div>
        </section>

        {{-- ============ FITUR (BENTO) ============ --}}
        <section id="features" class="scroll-mt-20 pb-20 pt-12 sm:pb-24 sm:pt-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl" data-reveal>
                    <h2 class="text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl">Semua urusan lahan, dalam satu aplikasi.</h2>
                    <p class="mt-4 text-lg text-stone-600">Dari mengenali penyakit sampai menjual hasil panen, tanpa berpindah-pindah aplikasi.</p>
                </div>

                <div class="mt-12 grid grid-cols-1 gap-4 lg:grid-cols-6 lg:grid-rows-[auto_auto_auto]">
                    {{-- AI Scanner: tile utama --}}
                    <article data-reveal class="group relative flex flex-col overflow-hidden rounded-2xl border border-stone-200/80 bg-stone-50 lg:col-span-4 lg:row-span-2 lg:flex-row">
                        <div class="flex flex-col justify-between p-6 sm:p-8 lg:w-1/2">
                            <div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-800 text-white">
                                    <i data-lucide="scan-search" class="h-5 w-5"></i>
                                </span>
                                <h3 class="mt-5 text-xl font-semibold text-stone-900">AI Scanner penyakit</h3>
                                <p class="mt-2 text-[15px] leading-relaxed text-stone-600">
                                    Arahkan kamera ke daun yang sakit. Dalam beberapa detik muncul nama penyakit,
                                    tingkat risiko, dan langkah penanganan yang bisa langsung dikerjakan.
                                </p>
                            </div>
                            <ul class="mt-6 space-y-2.5 text-sm text-stone-700">
                                <li class="flex items-center gap-2.5"><i data-lucide="check" class="h-4 w-4 text-brand-600"></i> Foto dari kamera atau galeri</li>
                                <li class="flex items-center gap-2.5"><i data-lucide="check" class="h-4 w-4 text-brand-600"></i> Saran penanganan ramah lingkungan</li>
                                <li class="flex items-center gap-2.5"><i data-lucide="check" class="h-4 w-4 text-brand-600"></i> Riwayat scan tersimpan rapi</li>
                            </ul>
                        </div>
                        <div class="relative min-h-[260px] lg:w-1/2">
                            <img src="{{ asset('assets/images/landing/scan-daun.jpg') }}" width="900" height="900" loading="lazy" decoding="async"
                                 alt="Ponsel memindai daun yang terserang penyakit"
                                 class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.03] motion-reduce:transition-none">
                        </div>
                    </article>

                    {{-- Cuaca --}}
                    <article data-reveal style="--delay: 80ms" class="group flex flex-col overflow-hidden rounded-2xl border border-stone-200/80 bg-white lg:col-span-2">
                        <div class="relative h-40 overflow-hidden">
                            <img src="{{ asset('assets/images/landing/sawah-terasering.jpg') }}" width="900" height="900" loading="lazy" decoding="async"
                                 alt="Sawah terasering di bawah langit cerah"
                                 class="h-full w-full object-cover object-[50%_65%] transition duration-700 group-hover:scale-[1.04] motion-reduce:transition-none">
                        </div>
                        <div class="p-6">
                            <h3 class="flex items-center gap-2 text-base font-semibold text-stone-900">
                                <i data-lucide="cloud-sun" class="h-5 w-5 text-brand-700"></i> Analisis cuaca
                            </h3>
                            <p class="mt-2 text-sm leading-relaxed text-stone-600">Prakiraan per kecamatan dan rekomendasi tanaman yang cocok untuk musim ini.</p>
                        </div>
                    </article>

                    {{-- Marketplace --}}
                    <article data-reveal style="--delay: 160ms" class="flex flex-col justify-between rounded-2xl border border-brand-100 bg-brand-50 p-6 lg:col-span-2">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-brand-700 ring-1 ring-inset ring-brand-200">
                            <i data-lucide="store" class="h-5 w-5"></i>
                        </span>
                        <div class="mt-8">
                            <h3 class="text-base font-semibold text-stone-900">Marketplace hasil panen</h3>
                            <p class="mt-2 text-sm leading-relaxed text-stone-600">Jual langsung ke pembeli tanpa perantara, lengkap dengan ulasan dari pembeli sebelumnya.</p>
                        </div>
                    </article>

                    {{-- Penyuluh --}}
                    <article data-reveal class="relative overflow-hidden rounded-2xl bg-brand-900 p-6 text-white sm:p-8 lg:col-span-3">
                        <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-brand-700/40 blur-3xl" aria-hidden="true"></div>
                        <span class="relative flex h-10 w-10 items-center justify-center rounded-lg bg-white/10 text-brand-200 ring-1 ring-inset ring-white/15">
                            <i data-lucide="users" class="h-5 w-5"></i>
                        </span>
                        <h3 class="relative mt-5 text-lg font-semibold">Didampingi penyuluh</h3>
                        <p class="relative mt-2 max-w-md text-[15px] leading-relaxed text-brand-100/85">
                            Hasil scan diteruskan ke penyuluh di wilayah Anda. Mereka meninjau, memberi rekomendasi,
                            dan menjadwalkan kunjungan ke lahan bila perlu.
                        </p>
                    </article>

                    {{-- Budidaya --}}
                    <article data-reveal style="--delay: 80ms" class="rounded-2xl border border-stone-200/80 bg-white p-6 sm:p-8 lg:col-span-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100">
                            <i data-lucide="book-open" class="h-5 w-5"></i>
                        </span>
                        <h3 class="mt-5 text-lg font-semibold text-stone-900">Panduan budidaya</h3>
                        <p class="mt-2 max-w-md text-[15px] leading-relaxed text-stone-600">
                            Langkah tanam, pemupukan, dan panen untuk komoditas utama seperti padi, cabai, jagung, dan tomat.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        {{-- ============ CARA KERJA ============ --}}
        <section id="cara-kerja" class="scroll-mt-20 border-y border-stone-200/70 bg-stone-50 py-20 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center" data-reveal>
                    <h2 class="text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl">Dari foto ke tindakan.</h2>
                    <p class="mt-4 text-lg text-stone-600">Tidak perlu menunggu petugas datang untuk tahu apa yang terjadi pada tanaman Anda.</p>
                </div>

                <div class="relative mt-14">
                <div class="absolute left-[16.6%] right-[16.6%] top-6 hidden h-px bg-gradient-to-r from-brand-200 via-brand-300 to-brand-200 md:block" aria-hidden="true"></div>
                <ol class="relative grid gap-10 md:grid-cols-3 md:gap-8">
                    @foreach ([
                        ['icon' => 'camera', 'judul' => 'Foto', 'teks' => 'Ambil foto daun, batang, atau buah yang bergejala langsung dari kamera HP atau galeri.'],
                        ['icon' => 'scan-search', 'judul' => 'Diagnosis', 'teks' => 'AI mengenali penyakitnya, memperkirakan tingkat risiko, dan menyusun langkah penanganan.'],
                        ['icon' => 'clipboard-list', 'judul' => 'Tindak lanjut', 'teks' => 'Laporan tersimpan dan diteruskan ke penyuluh wilayah untuk rekomendasi lanjutan.'],
                    ] as $langkah)
                        <li data-reveal style="--delay: {{ $loop->index * 100 }}ms" class="relative flex flex-col items-center text-center">
                            <span class="relative flex h-12 w-12 items-center justify-center rounded-full bg-white text-brand-700 shadow-card ring-1 ring-brand-200">
                                <i data-lucide="{{ $langkah['icon'] }}" class="h-5 w-5"></i>
                            </span>
                            <h3 class="mt-5 text-lg font-semibold text-stone-900">{{ $langkah['judul'] }}</h3>
                            <p class="mt-2 max-w-xs text-[15px] leading-relaxed text-stone-600">{{ $langkah['teks'] }}</p>
                        </li>
                    @endforeach
                </ol>
                </div>
            </div>
        </section>

        {{-- ============ PERAN ============ --}}
        <section class="py-20 sm:py-24">
            <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8">
                <div data-reveal class="order-2 lg:order-1">
                    <img src="{{ asset('assets/images/landing/petani-sawah.jpg') }}" width="900" height="900" loading="lazy" decoding="async"
                         alt="Petani tersenyum di tengah sawah"
                         class="aspect-[4/3] w-full rounded-3xl object-cover object-[50%_30%] ring-1 ring-stone-900/5">
                </div>
                <div class="order-1 lg:order-2">
                    <h2 data-reveal class="text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl">Dibuat untuk semua yang terlibat di lahan.</h2>
                    <dl class="mt-10 space-y-8">
                        @foreach ([
                            ['icon' => 'sprout', 'peran' => 'Petani', 'teks' => 'Scan penyakit, kelola lahan, dan jual hasil panen dari satu akun.'],
                            ['icon' => 'user-check', 'peran' => 'Penyuluh', 'teks' => 'Pantau laporan penyakit di wilayah binaan, jadwalkan kunjungan, dan kirim rekomendasi.'],
                            ['icon' => 'shopping-basket', 'peran' => 'Pembeli', 'teks' => 'Beli hasil panen segar langsung dari petani lokal, lalu beri ulasan.'],
                        ] as $item)
                            <div data-reveal style="--delay: {{ $loop->index * 90 }}ms" class="flex gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100">
                                    <i data-lucide="{{ $item['icon'] }}" class="h-5 w-5"></i>
                                </span>
                                <div>
                                    <dt class="text-base font-semibold text-stone-900">{{ $item['peran'] }}</dt>
                                    <dd class="mt-1 text-[15px] leading-relaxed text-stone-600">{{ $item['teks'] }}</dd>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </section>

        {{-- ============ TENTANG ============ --}}
        <section id="about" class="scroll-mt-16 px-4 sm:px-6 lg:px-8">
            <div class="relative mx-auto max-w-7xl overflow-hidden rounded-3xl">
                <img src="{{ asset('assets/images/landing/panen-padi.jpg') }}" width="1376" height="768" loading="lazy" decoding="async"
                     alt="Petani memeriksa padi yang menguning di sawah"
                     class="absolute inset-0 h-full w-full object-cover object-[70%_50%]">
                {{-- Scrim: merata di HP, gradasi ke kanan di desktop supaya petani di foto tetap terlihat --}}
                <div class="absolute inset-0 bg-brand-950/80 lg:bg-transparent lg:bg-gradient-to-r lg:from-brand-950/95 lg:via-brand-950/75 lg:to-transparent" aria-hidden="true"></div>

                <div class="relative max-w-2xl px-6 py-16 sm:px-12 sm:py-20 lg:px-16 lg:py-24">
                    <div data-reveal>
                        <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Tentang AgriScan</h2>
                        <p class="mt-5 text-lg leading-relaxed text-stone-200">
                            AgriScan menjembatani petani, penyuluh pertanian, dan teknologi. Data yang akurat dan diagnosis yang cepat
                            membantu petani mengambil keputusan lebih baik untuk lahan dan tanamannya.
                        </p>
                    </div>
                    <dl class="mt-10 grid gap-6 sm:grid-cols-2">
                        <div data-reveal class="border-l-2 border-brand-400 pl-5">
                            <dt class="text-sm font-semibold text-brand-200">Misi</dt>
                            <dd class="mt-1 text-[15px] leading-relaxed text-white">Memberdayakan petani Indonesia dengan data dan teknologi yang mudah diakses.</dd>
                        </div>
                        <div data-reveal style="--delay: 100ms" class="border-l-2 border-brand-400 pl-5">
                            <dt class="text-sm font-semibold text-brand-200">Visi</dt>
                            <dd class="mt-1 text-[15px] leading-relaxed text-white">Mewujudkan pertanian Indonesia yang lebih modern, efisien, dan berkelanjutan.</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        {{-- ============ KONTAK + CTA ============ --}}
        <section id="contact" class="scroll-mt-16 py-20 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-12 lg:gap-8">
                    <div class="lg:col-span-5" data-reveal>
                        <h2 class="text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl">Ada pertanyaan?</h2>
                        <p class="mt-4 max-w-md text-lg text-stone-600">Tim kami siap membantu, baik soal akun, data lahan, maupun kerja sama dengan dinas.</p>
                    </div>
                    <ul class="grid gap-4 sm:grid-cols-3 lg:col-span-7">
                        @foreach ([
                            ['icon' => 'mail', 'label' => 'Email', 'isi' => 'info@agridata.id', 'href' => 'mailto:info@agridata.id'],
                            ['icon' => 'phone', 'label' => 'Telepon', 'isi' => '(0351) 123-4567', 'href' => 'tel:+623511234567'],
                            ['icon' => 'map-pin', 'label' => 'Alamat', 'isi' => 'Madiun, Jawa Timur', 'href' => null],
                        ] as $kontak)
                            <li data-reveal style="--delay: {{ $loop->index * 80 }}ms" class="rounded-2xl border border-stone-200/80 bg-white p-5">
                                <i data-lucide="{{ $kontak['icon'] }}" class="h-5 w-5 text-brand-700"></i>
                                <p class="mt-4 text-sm font-medium text-stone-500">{{ $kontak['label'] }}</p>
                                @if ($kontak['href'])
                                    <a href="{{ $kontak['href'] }}" class="mt-0.5 block break-words text-[15px] font-semibold text-stone-900 hover:text-brand-800">{{ $kontak['isi'] }}</a>
                                @else
                                    <p class="mt-0.5 text-[15px] font-semibold text-stone-900">{{ $kontak['isi'] }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div data-reveal class="relative mt-16 overflow-hidden rounded-3xl bg-brand-900 px-6 py-12 sm:px-12 sm:py-14 lg:flex lg:items-center lg:justify-between lg:gap-8 lg:px-16">
                    <div class="pointer-events-none absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-brand-600/30 blur-3xl" aria-hidden="true"></div>
                    <div class="relative">
                        <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">Mulai pantau tanaman Anda hari ini.</h2>
                        <p class="mt-3 max-w-xl text-base text-brand-100/85">Gratis untuk petani dan pembeli. Penyuluh didaftarkan langsung oleh dinas.</p>
                    </div>
                    <div class="relative mt-8 shrink-0 lg:mt-0">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-base font-semibold text-brand-900 shadow-sm transition hover:bg-brand-50 active:translate-y-px">
                                Buka dashboard <i data-lucide="arrow-right" class="h-5 w-5"></i>
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-base font-semibold text-brand-900 shadow-sm transition hover:bg-brand-50 active:translate-y-px">
                                Daftar gratis <i data-lucide="arrow-right" class="h-5 w-5"></i>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- ============ FOOTER ============ --}}
    <footer class="border-t border-stone-200/70">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-10 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
            <div>
                <a href="#atas" class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-800 text-brand-100">
                        <i data-lucide="sprout" class="h-[18px] w-[18px]"></i>
                    </span>
                    <span class="text-[15px] font-bold tracking-tight">AgriScan</span>
                </a>
                <p class="mt-3 text-sm text-stone-500">&copy; {{ date('Y') }} Dinas Pertanian &amp; AgriScan. Hak cipta dilindungi undang-undang.</p>
            </div>
            <ul class="flex flex-wrap gap-x-6 gap-y-2 text-sm font-medium text-stone-600">
                <li><a href="#features" class="hover:text-stone-900">Fitur</a></li>
                <li><a href="#cara-kerja" class="hover:text-stone-900">Cara kerja</a></li>
                <li><a href="#about" class="hover:text-stone-900">Tentang</a></li>
                <li><a href="#contact" class="hover:text-stone-900">Kontak</a></li>
                <li><a href="{{ route('login') }}" class="hover:text-stone-900">Masuk</a></li>
            </ul>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        (function () {
            // Menu HP
            const tombol = document.getElementById('tombol-menu');
            const menu = document.getElementById('menu-hp');
            const ikonBuka = tombol.querySelector('[data-ikon-buka]');
            const ikonTutup = tombol.querySelector('[data-ikon-tutup]');
            const kelasTertutup = ['opacity-0', '-translate-y-2', 'pointer-events-none'];

            const atur = (buka) => {
                kelasTertutup.forEach((k) => menu.classList.toggle(k, !buka));
                tombol.setAttribute('aria-expanded', String(buka));
                tombol.setAttribute('aria-label', buka ? 'Tutup menu' : 'Buka menu');
                ikonBuka.classList.toggle('hidden', buka);
                ikonTutup.classList.toggle('hidden', !buka);
            };

            tombol.addEventListener('click', () => atur(tombol.getAttribute('aria-expanded') !== 'true'));
            menu.querySelectorAll('[data-tutup-menu]').forEach((a) => a.addEventListener('click', () => atur(false)));
            document.addEventListener('keydown', (e) => e.key === 'Escape' && atur(false));
            window.matchMedia('(min-width: 1024px)').addEventListener('change', () => atur(false));

            if (!('IntersectionObserver' in window)) {
                document.querySelectorAll('[data-reveal]').forEach((el) => el.classList.add('terlihat'));
                return;
            }

            // Garis bawah navbar muncul begitu halaman mulai digulir
            const header = document.getElementById('atas');
            new IntersectionObserver(([entri]) => {
                header.classList.toggle('border-stone-200/80', !entri.isIntersecting);
                header.classList.toggle('border-transparent', entri.isIntersecting);
            }).observe(document.getElementById('penanda-atas'));

            // Animasi muncul saat elemen masuk layar
            const pengamat = new IntersectionObserver((entri) => {
                entri.forEach((e) => {
                    if (!e.isIntersecting) return;
                    e.target.classList.add('terlihat');
                    pengamat.unobserve(e.target);
                });
            }, { rootMargin: '0px 0px -10% 0px' });

            document.querySelectorAll('[data-reveal]').forEach((el) => pengamat.observe(el));
        })();
    </script>
</body>
</html>
