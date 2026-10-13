<!DOCTYPE html>
<html lang="id" class="h-full bg-stone-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f2219">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'AgriScan') }}</title>

    @include('partials.head-assets')

    {{-- Font Awesome masih dipakai halaman Analisis Cuaca --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>
<body class="h-full font-sans text-stone-900 antialiased">
    <a href="#konten"
       class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:shadow-lift">
        Lewati ke konten
    </a>

    @include('partials.sidebar')

    <div class="flex min-h-full flex-col lg:pl-72">
        @include('partials.navbar')

        <main id="konten" class="flex-1">
            <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="{{ asset('assets/js/app-shell.js') }}"></script>
    <script>
        lucide.createIcons();
    </script>

    @stack('scripts')
</body>
</html>
