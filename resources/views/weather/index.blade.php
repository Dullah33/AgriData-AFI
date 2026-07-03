@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-blue-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Analisis Cuaca & Jadwal Tanam</h1>
            <p class="text-gray-600">Analisis kondisi cuaca lokal untuk rekomendasi jadwal tanam optimal</p>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- KOLOM KIRI: Form Input -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 sticky top-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        Parameter Lokasi
                    </h2>
                    
                    <div class="space-y-4">
                        <!-- Provinsi -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Provinsi</label>
                            <select id="provinsi" 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all bg-white disabled:bg-gray-100 disabled:cursor-not-allowed">
                                <option value="">Memuat provinsi...</option>
                            </select>
                        </div>

                        <!-- Kabupaten -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kabupaten/Kota</label>
                            <select id="kabupaten" 
                                disabled
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all bg-gray-100 disabled:cursor-not-allowed">
                                <option value="">-- Pilih Kabupaten --</option>
                            </select>
                        </div>

                        <!-- Kecamatan -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kecamatan</label>
                            <select id="kecamatan" 
                                disabled
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all bg-gray-100 disabled:cursor-not-allowed">
                                <option value="">-- Pilih Kecamatan --</option>
                            </select>
                        </div>

                        <hr class="border-gray-200 my-6">

                        <!-- Tanaman (Opsional) -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanaman (Opsional)</label>
                            <select id="tanamanSelect" 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all bg-white">
                                <option value="">--- Rekomendasikan untuk saya ---</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Kosongkan untuk melihat top 5 rekomendasi</p>
                        </div>

                        <!-- Tombol Analisis -->
                        <button id="btnAnalisis" 
                            disabled
                            class="w-full mt-6 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                            <i data-lucide="search" class="w-5 h-5"></i>
                            Analisis Sekarang
                        </button>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: Panel Hasil -->
            <div class="lg:col-span-2">
                <div id="panelHasil" class="bg-white rounded-2xl shadow-lg border border-gray-200 min-h-[600px] p-6 relative overflow-hidden">
                    
                    <!-- STATE 1: IDLE (Default) -->
                    <div id="stateIdle" class="flex flex-col items-center justify-center h-full text-center text-gray-500">
                        <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="cloud-sun" class="w-12 h-12 text-blue-300"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">Siap Menganalisis</h3>
                        <p class="text-sm max-w-md">Pilih lokasi dan tanaman (opsional), lalu klik tombol "Analisis Sekarang" untuk melihat rekomendasi jadwal tanam berdasarkan kondisi cuaca real-time.</p>
                    </div>

                    <!-- STATE 2: SKELETON LOADER (Background) -->
                    <div id="skeletonLoader" class="hidden absolute inset-0 p-6 bg-white z-10">
                        <div class="space-y-4 animate-pulse">
                            <div class="h-6 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                            <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                        </div>
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <div class="h-32 bg-gray-200 rounded-xl animate-pulse"></div>
                            <div class="h-32 bg-gray-200 rounded-xl animate-pulse"></div>
                        </div>
                        <div class="mt-4 h-24 bg-gray-200 rounded-xl animate-pulse"></div>
                    </div>

                    <!-- STATE 3: FLOATING LOADING CARD (Overlay) -->
                    <div id="floatingLoadingCard" class="hidden absolute inset-0 flex items-center justify-center z-50 bg-white/60 backdrop-blur-sm">
                        <div class="bg-white p-6 rounded-2xl shadow-2xl w-96 max-w-[90%] border border-gray-200">
                            <!-- Header -->
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-2 bg-blue-50 rounded-lg">
                                    <i data-lucide="loader-2" class="w-6 h-6 text-blue-600 animate-spin"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800">Sedang Menganalisis...</h4>
                                    <p class="text-xs text-gray-500">Mohon tunggu sebentar</p>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="w-full bg-gray-100 rounded-full h-3 mb-4 overflow-hidden">
                                <div id="progressBar" class="bg-gradient-to-r from-blue-500 to-emerald-500 h-3 rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
                            </div>
                            
                            <!-- Step Indicators -->
                            <ul class="text-sm text-gray-600 space-y-3">
                                <li id="step1" class="flex items-center gap-3 font-medium text-blue-600">
                                    <i class="fas fa-circle-notch fa-spin w-4"></i> 
                                    <span>Mengonversi lokasi ke koordinat...</span>
                                </li>
                                <li id="step2" class="flex items-center gap-3 text-gray-400">
                                    <i class="far fa-circle w-4"></i> 
                                    <span>Mengambil data cuaca real-time...</span>
                                </li>
                                <li id="step3" class="flex items-center gap-3 text-gray-400">
                                    <i class="far fa-circle w-4"></i> 
                                    <span>Mencocokkan dengan database tanaman...</span>
                                </li>
                                <li id="step4" class="flex items-center gap-3 text-gray-400">
                                    <i class="far fa-circle w-4"></i> 
                                    <span>Menyusun rekomendasi jadwal tanam...</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- STATE 4: RESULT CONTENT (Dynamic) -->
                    <div id="resultContent" class="relative z-20"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include JavaScript -->
<script src="{{ asset('assets/js/weather-analysis.js') }}"></script>
@endsection