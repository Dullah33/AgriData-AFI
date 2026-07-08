<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'AgriData') }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Font Awesome (untuk icon) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        {{-- Sidebar (sempat kehilangan baris @include ini saat rombak layout untuk fitur budidaya) --}}
        @include('partials.sidebar')

        <div id="main-content" class="flex-1 flex flex-col transition-[margin] duration-300 ease-in-out">
            <!-- Navbar -->
            <nav class="bg-white shadow-md border-b border-gray-200">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <!-- Tombol hamburger: toggle sidebar, aktif di semua ukuran layar (desktop & mobile) -->
                        <div class="flex items-center gap-3">
                            <button id="sidebar-toggle" onclick="toggleSidebar()"
                                    class="text-gray-600 hover:text-gray-900 p-1 -ml-1">
                                <i data-lucide="menu" class="w-6 h-6"></i>
                            </button>
                            <h1 class="font-semibold text-lg text-gray-800">@yield('title', 'Dashboard')</h1>
                        </div>

                        <!-- User Menu -->
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-600">{{ auth()->user()->username }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-gray-600 hover:text-red-600 transition">
                                    <i data-lucide="log-out" class="w-5 h-5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="py-6 px-4 sm:px-6 lg:px-8 flex-1">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();

        // Toggle sidebar — bekerja di semua ukuran layar.
        // Desktop (>=1024px): konten ikut bergeser (margin-left), tanpa overlay.
        // Mobile (<1024px): sidebar menimpa konten sebagai overlay + latar gelap.
        function toggleSidebar() {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebar-overlay');
            var main = document.getElementById('main-content');
            var isDesktop = window.innerWidth >= 1024;

            sidebar.classList.toggle('-translate-x-full');

            if (isDesktop) {
                main.classList.toggle('lg:ml-64');
            } else {
                overlay.classList.toggle('hidden');
            }
        }

        // Default: sidebar otomatis terbuka di layar besar, tertutup di layar kecil.
        document.addEventListener('DOMContentLoaded', function () {
            if (window.innerWidth >= 1024) {
                document.getElementById('sidebar').classList.remove('-translate-x-full');
                document.getElementById('main-content').classList.add('lg:ml-64');
            }
        });
    </script>
</body>
</html>