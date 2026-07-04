@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#eff6ff]">
    
    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i data-lucide="leaf" class="w-8 h-8 text-[#1e40af]"></i>
                <span class="font-bold text-2xl text-gray-800">AgriData</span>
            </div>
            <ul class="flex gap-2 list-none">
                <li><a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-[#1e40af] px-4 py-2 rounded-lg transition font-medium">Beranda</a></li>
                <li><a href="{{ route('budidaya.index') }}" class="text-white bg-[#4f8a5b] px-4 py-2 rounded-lg transition font-medium">Budidaya</a></li>
                <li><a href="{{ route('cuaca.index') }}" class="text-gray-600 hover:text-[#1e40af] px-4 py-2 rounded-lg transition font-medium">Analisis</a></li>
                <li><a href="#" class="text-gray-600 hover:text-[#1e40af] px-4 py-2 rounded-lg transition font-medium">Jenis Tanaman</a></li>
            </ul>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#1e40af] rounded-full flex items-center justify-center text-white font-semibold">
                    {{ substr(auth()->user()->username, 0, 1) }}
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-[#1e40af] to-[#3b82f6] text-white py-16">
        <div class="max-w-4xl mx-auto text-center px-6">
            <span class="inline-block bg-white/20 border border-white/30 text-white px-4 py-1.5 rounded-full text-sm font-semibold mb-4">
                {{ $plant->musim ?? 'Semua Musim' }}
            </span>
            <h1 class="text-4xl font-bold mb-3">{{ $plant->nama }}</h1>
            <p class="text-lg opacity-90 max-w-2xl mx-auto">{{ $plant->deskripsi ?? 'Panduan budidaya lengkap' }}</p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-6 py-12">
        
        <!-- Breadcrumbs -->
        <nav class="text-sm text-gray-500 mb-8">
            <a href="{{ route('dashboard') }}" class="hover:text-[#1e40af]">Dashboard</a> 
            <span class="mx-2">/</span>
            <a href="{{ route('budidaya.index') }}" class="hover:text-[#1e40af]">Budidaya</a> 
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-medium">{{ $plant->nama }}</span>
        </nav>

        <!-- Info Cards Section -->
        <section class="mb-16">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Informasi Budidaya</h2>
                <div class="w-16 h-1 mx-auto bg-gradient-to-r from-[#4f8a5b] to-[#1e40af] rounded"></div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: Persiapan Lahan -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all">
                    <div class="w-12 h-12 bg-[#dcfce7] rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="sprout" class="w-6 h-6 text-[#166534]"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Persiapan Lahan</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ $plant->budidaya_persiapan_lahan ?? 'Informasi persiapan lahan akan tersedia segera.' }}
                    </p>
                </div>

                <!-- Card 2: Pemupukan -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all">
                    <div class="w-12 h-12 bg-[#dbeafe] rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="droplet" class="w-6 h-6 text-[#1e40af]"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Pemupukan & Nutrisi</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ $plant->budidaya_pemupukan ?? 'Informasi pemupukan akan tersedia segera.' }}
                    </p>
                </div>

                <!-- Card 3: Irigasi -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all">
                    <div class="w-12 h-12 bg-[#cffafe] rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="droplets" class="w-6 h-6 text-[#0e7490]"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Irigasi & Penyiraman</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ $plant->budidaya_irigasi ?? 'Informasi irigasi akan tersedia segera.' }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Timeline Steps Section -->
        <section class="mb-16">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Tahapan Budidaya</h2>
                <div class="w-16 h-1 mx-auto bg-gradient-to-r from-[#4f8a5b] to-[#1e40af] rounded"></div>
                <p class="text-gray-600 text-sm mt-2">Ikuti langkah-langkah berikut untuk hasil optimal</p>
            </div>

            <div class="relative max-w-3xl mx-auto">
                <!-- Timeline Line -->
                <div class="absolute left-6 md:left-7 top-0 bottom-0 w-0.5 bg-gradient-to-b from-[#4f8a5b] via-[#1e40af] to-[#3b82f6]"></div>

                @php
                    $steps = is_string($plant->langkah_budidaya)
                        ? json_decode($plant->langkah_budidaya, true)
                        : ($plant->langkah_budidaya ?? []);
                @endphp

                @forelse($steps as $index => $step)
                <div class="flex gap-6 mb-8 relative fade-up">
                    <!-- Step Number -->
                    <div class="w-12 h-12 rounded-full bg-white border-4 border-[#4f8a5b] flex items-center justify-center font-bold text-[#4f8a5b] z-10 shrink-0">
                        {{ $index + 1 }}
                    </div>
                    
                    <!-- Step Content -->
                    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-5 flex-1 hover:shadow-xl transition-all">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $step['title'] ?? 'Langkah ' . ($index + 1) }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ $step['content'] ?? 'Deskripsi langkah akan tersedia segera.' }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 bg-white rounded-2xl border border-gray-200">
                    <p class="text-gray-500">Panduan tahapan budidaya untuk {{ $plant->nama }} akan segera tersedia.</p>
                </div>
                @endforelse
            </div>
        </section>

        <!-- Tips Section -->
        <section class="mb-12">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Tips Sukses</h2>
                <div class="w-16 h-1 mx-auto bg-gradient-to-r from-[#4f8a5b] to-[#1e40af] rounded"></div>
            </div>

            <div class="flex flex-wrap justify-center gap-4 max-w-4xl mx-auto">
                @php
                    $tips = is_string($plant->tips_budidaya) 
                        ? json_decode($plant->tips_budidaya, true) 
                        : ($plant->tips_budidaya ?? []);
                    if (!is_array($tips)) $tips = [];
                @endphp
                @forelse($tips as $tip)
                <div class="bg-white rounded-xl border-l-4 border-[#4f8a5b] p-5 shadow hover:shadow-md transition-all w-full sm:w-[calc(50%-0.5rem)] lg:w-[calc(33.333%-0.667rem)] max-w-sm">
                    <div class="text-2xl mb-2">{{ $tip['icon'] ?? '💡' }}</div>
                    <h3 class="font-semibold text-gray-800 mb-1">{{ $tip['title'] ?? 'Tips' }}</h3>
                    <p class="text-gray-600 text-sm">{{ $tip['content'] ?? 'Deskripsi tips akan tersedia segera.' }}</p>
                </div>
                @empty
                <div class="w-full text-center py-8 bg-white rounded-xl border border-gray-200">
                    <p class="text-gray-500">Tips budidaya akan segera ditambahkan.</p>
                </div>
                @endforelse
            </div>
        </section>

        <!-- CTA Section -->
        <section class="text-center py-10 bg-gradient-to-r from-[#4f8a5b] to-[#1e40af] rounded-3xl text-white">
            <h2 class="text-2xl font-bold mb-3">Mulai Budidaya {{ $plant->nama }} Sekarang!</h2>
            <p class="opacity-90 mb-6 max-w-md mx-auto">Dengan teknik yang tepat, Anda bisa menghasilkan panen berkualitas dari lahan sendiri.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('cuaca.index') }}" class="px-6 py-3 bg-white text-[#1e40af] font-semibold rounded-xl hover:bg-blue-50 transition-all no-underline">
                    Kembali ke Analisis Cuaca
                </a>
                <a href="{{ route('budidaya.index') }}" class="px-6 py-3 bg-[#1e40af] text-white font-semibold rounded-xl hover:bg-blue-800 transition-all no-underline">
                    Lihat Tanaman Lain
                </a>
            </div>
        </section>

    </main>
</div>

<!-- Scroll Animation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Langsung tampilkan semua elemen fade-up (tanpa animation observer)
    document.querySelectorAll('.fade-up').forEach(el => {
        el.style.opacity = '1';
        el.style.transform = 'translateY(0)';
    });
    
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-up').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
});
</script>
@endsection