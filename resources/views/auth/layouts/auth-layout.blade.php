<!DOCTYPE html>
<html lang="id" class="h-full bg-stone-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f2219">
    <title>@yield('title') - AgriScan</title>

    @include('partials.head-assets')

    {{--
        Kelas di bawah dipakai langsung oleh halaman login & daftar. Namanya
        dipertahankan supaya markup dan validasi form di halaman tersebut
        tidak perlu diubah, tapi tampilannya disesuaikan dengan palet baru:
        kartu putih bersih, fokus input hijau, tombol solid tanpa gradasi.
    --}}
    <style>
        .glass-panel {
            background: #fff;
            border: 1px solid rgb(231 229 228 / .8);
            box-shadow: 0 1px 2px rgb(15 34 25 / .04), 0 16px 40px -16px rgb(15 34 25 / .16);
        }
        .glass-panel.rounded-\[32px\] {
            border-radius: 1.5rem;
        }
        @media (max-width: 639px) {
            .glass-panel.p-10 {
                padding: 1.75rem 1.25rem;
            }
        }
        .input-glow {
            background-color: #fff !important;
            border-color: #d6d3d1;
        }
        .input-glow:focus {
            border-color: #4a8a63;
            box-shadow: 0 0 0 3px rgb(74 138 99 / .18);
        }
        .btn-gradient {
            background: #2d5940;
            border-radius: .5rem !important;
            box-shadow: 0 1px 2px rgb(15 34 25 / .12) !important;
            transition: background-color .15s ease, transform .15s ease;
        }
        .btn-gradient:hover {
            background: #264835;
        }
        .btn-gradient:active {
            transform: translateY(1px);
        }
    </style>
</head>
<body class="h-full font-sans text-stone-900 antialiased">
    <div class="grid min-h-[100dvh] lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
        {{-- Form. Logo & link kembali ke beranda sudah ada di dalam kartu form. --}}
        <main class="flex items-center justify-center px-4 py-10 sm:px-6 lg:px-12">
            @yield('content')
        </main>

        {{-- Foto (desktop) --}}
        <aside class="relative hidden overflow-hidden lg:block" aria-hidden="true">
            <img src="{{ asset('assets/images/landing/panen-padi.jpg') }}" alt="" width="1376" height="768"
                 class="absolute inset-0 h-full w-full object-cover object-[72%_50%]">
            <div class="absolute inset-0 bg-gradient-to-t from-brand-950/90 via-brand-950/30 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-12 xl:p-16">
                <p class="max-w-md text-2xl font-semibold leading-snug tracking-tight text-white">
                    Kenali penyakit tanaman sejak dini, dan dapatkan pendampingan penyuluh di wilayah Anda.
                </p>
                <p class="mt-4 text-sm font-medium text-brand-200">AgriScan untuk petani, penyuluh, dan pembeli</p>
            </div>
        </aside>
    </div>

    <script>
        lucide.createIcons();
    </script>

    @stack('scripts')
</body>
</html>
