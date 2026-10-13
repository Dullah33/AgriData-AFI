{{--
    Komponen AI Scanner Penyakit Tanaman (dipakai halaman scanner Petani & User).

    Props: action (URL tujuan form) dan batal-url (opsional). Isi slot
    dipakai untuk field tambahan di atas area foto, mis. pilihan lahan.

    Interaksi (kamera WebRTC, kompres foto, upload + animasi pemindaian) diatur
    oleh komponen Alpine "scannerPenyakit" di public/assets/js/scanner-penyakit.js.
    Kalau JavaScript gagal dimuat, elemen x-cloak tetap tersembunyi dan form
    masih bisa dipakai sebagai form upload biasa.
--}}
@props(['action', 'batalUrl' => null])

<div x-data="scannerPenyakit(@js(['galat' => $errors->first('foto_tanaman')]))">
    <form x-ref="form" method="POST" action="{{ $action }}" enctype="multipart/form-data"
          x-bind:novalidate="true" @submit.prevent="kirim" class="space-y-5">
        @csrf

        {{ $slot }}

        {{-- Pilihan sumber foto --}}
        <div x-cloak role="tablist" aria-label="Sumber foto" class="grid grid-cols-2 gap-1 p-1 bg-gray-100 rounded-xl">
            <button type="button" role="tab" @click="pilihMode('galeri')" :disabled="sedangProses"
                    :aria-selected="mode === 'galeri'"
                    :class="mode === 'galeri' ? 'bg-white text-green-700 shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                    class="flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold transition disabled:cursor-not-allowed">
                <i data-lucide="image" class="w-4 h-4"></i>
                <span class="sm:hidden">Galeri</span><span class="hidden sm:inline">Unggah dari Galeri</span>
            </button>
            <button type="button" role="tab" @click="pilihMode('kamera')" :disabled="sedangProses"
                    :aria-selected="mode === 'kamera'"
                    :class="mode === 'kamera' ? 'bg-white text-green-700 shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                    class="flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold transition disabled:cursor-not-allowed">
                <i data-lucide="camera" class="w-4 h-4"></i>
                <span class="sm:hidden">Kamera</span><span class="hidden sm:inline">Ambil dari Kamera</span>
            </button>
        </div>

        {{-- Area tampilan: dropzone / kamera live / pratinjau / animasi scan.
             Potret (3:4) di HP karena kamera HP umumnya merekam tegak. --}}
        <div x-ref="panggung" class="relative w-full aspect-[3/4] sm:aspect-[4/3] rounded-2xl overflow-hidden border-2 transition-colors"
             :class="(mode === 'kamera' || pratinjau)
                        ? 'border-transparent bg-gray-900'
                        : (seret ? 'border-dashed border-green-500 bg-green-50' : 'border-dashed border-gray-300 bg-gray-50')">

            {{-- Dropzone galeri (juga jadi form upload biasa kalau JS tidak jalan) --}}
            <label for="foto_tanaman" x-show="mode === 'galeri' && !pratinjau"
                   @dragover.prevent="seret = true" @dragleave.prevent="seret = false" @drop.prevent="terimaDrop($event)"
                   class="absolute inset-0 flex flex-col items-center justify-center text-center px-6 cursor-pointer group">
                <span class="w-14 h-14 mb-3 rounded-full bg-green-100 text-green-600 flex items-center justify-center group-hover:scale-105 transition">
                    <i data-lucide="image-up" class="w-7 h-7"></i>
                </span>
                <span x-show="!menyiapkan" class="text-sm font-semibold text-gray-700">Klik untuk memilih foto tanaman</span>
                <span x-cloak x-show="menyiapkan" class="text-sm font-semibold text-gray-700">Menyiapkan foto…</span>
                <span class="mt-1 text-xs text-gray-500">
                    <span class="hidden sm:inline">atau seret &amp; lepas foto ke sini · </span>JPG, PNG, WEBP · maks. 5 MB
                </span>
            </label>

            {{-- Kamera live (WebRTC) --}}
            <video x-ref="video" x-cloak x-show="mode === 'kamera' && !pratinjau" playsinline muted autoplay
                   :style="arahKamera === 'user' ? 'transform: scaleX(-1)' : ''"
                   class="absolute inset-0 w-full h-full object-cover"></video>

            {{-- Bingkai bidik saat kamera aktif --}}
            <div x-cloak x-show="mode === 'kamera' && kameraAktif && !pratinjau" class="pointer-events-none absolute inset-0">
                <div class="absolute inset-8 sm:inset-12">
                    <span class="scan-sudut top-0 left-0 border-t-4 border-l-4 rounded-tl-xl"></span>
                    <span class="scan-sudut top-0 right-0 border-t-4 border-r-4 rounded-tr-xl"></span>
                    <span class="scan-sudut bottom-0 left-0 border-b-4 border-l-4 rounded-bl-xl"></span>
                    <span class="scan-sudut bottom-0 right-0 border-b-4 border-r-4 rounded-br-xl"></span>
                </div>
                <p class="absolute top-3 inset-x-0 text-center">
                    <span class="px-3 py-1 rounded-full bg-black/55 text-white text-xs">Arahkan ke daun atau bagian yang sakit</span>
                </p>
            </div>

            {{-- Status kamera: sedang dinyalakan / gagal --}}
            <div x-cloak x-show="mode === 'kamera' && !kameraAktif && !pratinjau"
                 class="absolute inset-0 flex flex-col items-center justify-center text-center px-6 text-white">
                <div x-show="memuatKamera" class="flex flex-col items-center">
                    <svg class="animate-spin w-8 h-8 text-green-400 mb-3" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle>
                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                    <p class="text-sm">Menyalakan kamera…</p>
                    <p class="text-xs text-white/60 mt-1">Izinkan akses kamera jika browser meminta.</p>
                </div>
                <div x-show="!memuatKamera" class="flex flex-col items-center max-w-sm">
                    <span class="w-12 h-12 mb-3 rounded-full bg-white/10 flex items-center justify-center">
                        <i data-lucide="camera-off" class="w-6 h-6"></i>
                    </span>
                    <p class="text-sm" x-text="galatKamera || 'Kamera belum aktif.'"></p>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <button type="button" x-show="dukungKameraLangsung" @click="nyalakanKamera()"
                                class="px-4 py-2 rounded-lg bg-white text-gray-800 text-sm font-semibold hover:bg-gray-100">
                            Nyalakan Kamera
                        </button>
                        <button type="button" @click="$refs.inputKamera.click()"
                                class="px-4 py-2 rounded-lg bg-white/15 text-white text-sm font-semibold hover:bg-white/25">
                            Pakai Kamera Bawaan HP
                        </button>
                    </div>
                </div>
            </div>

            {{-- Pratinjau foto terpilih --}}
            <img x-cloak x-show="pratinjau" :src="pratinjau" alt="Pratinjau foto tanaman"
                 class="absolute inset-0 w-full h-full object-contain">

            <span x-cloak x-show="pratinjau && !sedangProses" x-text="infoBerkas"
                  class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-black/60 text-white text-xs"></span>

            {{-- Efek kilat saat foto diambil --}}
            <div x-cloak x-show="kilat" x-transition.opacity.duration.250ms class="absolute inset-0 bg-white"></div>

            {{-- Animasi pemindaian selama upload & analisis --}}
            <div x-cloak x-show="sedangProses" x-transition.opacity class="absolute inset-0 scan-overlay" aria-hidden="true">
                <div class="scan-grid"></div>
                <div class="scan-garis"></div>
                <span class="scan-titik" style="top: 28%; left: 32%;"></span>
                <span class="scan-titik" style="top: 55%; left: 68%; animation-delay: .7s;"></span>
                <span class="scan-titik" style="top: 70%; left: 40%; animation-delay: 1.4s;"></span>
                <div class="absolute inset-6 sm:inset-10">
                    <span class="scan-sudut top-0 left-0 border-t-4 border-l-4 rounded-tl-xl"></span>
                    <span class="scan-sudut top-0 right-0 border-t-4 border-r-4 rounded-tr-xl"></span>
                    <span class="scan-sudut bottom-0 left-0 border-b-4 border-l-4 rounded-bl-xl"></span>
                    <span class="scan-sudut bottom-0 right-0 border-b-4 border-r-4 rounded-br-xl"></span>
                </div>
            </div>
            <div x-cloak x-show="sedangProses"
                 class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/85 via-black/60 to-transparent text-white">
                <div class="flex items-center gap-3">
                    <svg class="animate-spin w-5 h-5 shrink-0 text-green-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle>
                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                    <div class="min-w-0" role="status" aria-live="polite">
                        <p class="text-sm font-semibold truncate" x-text="labelTahap"></p>
                        <p class="text-xs text-white/70 truncate" x-text="keteranganTahap"></p>
                    </div>
                </div>
                <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
                    <div class="h-full rounded-full bg-green-400 transition-[width] duration-500 ease-out"
                         :style="`width: ${progresTampil}%`"></div>
                </div>
            </div>
        </div>

        {{-- Tombol rana kamera --}}
        <div x-ref="rana" x-cloak x-show="mode === 'kamera' && kameraAktif && !pratinjau" class="grid grid-cols-3 items-center scroll-mb-4">
            <span></span>
            <button type="button" @click="jepret()" aria-label="Ambil foto"
                    class="justify-self-center w-16 h-16 rounded-full bg-white border-4 border-green-600 ring-4 ring-green-100 shadow-md hover:scale-105 active:scale-95 transition flex items-center justify-center">
                <span class="w-11 h-11 rounded-full bg-green-600"></span>
            </button>
            <button type="button" x-show="jumlahKamera > 1" @click="gantiKamera()" aria-label="Ganti kamera depan/belakang"
                    class="justify-self-end w-11 h-11 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center justify-center">
                <i data-lucide="switch-camera" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- Aksi setelah foto dipilih --}}
        <div x-cloak x-show="pratinjau && !sedangProses" class="flex items-center justify-between gap-3 text-sm">
            <p class="text-gray-500 truncate" x-text="berkas ? berkas.name : ''"></p>
            <button type="button" @click="ulangi()"
                    class="shrink-0 inline-flex items-center gap-1.5 font-semibold text-gray-700 hover:text-green-700">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                <span x-text="mode === 'kamera' ? 'Foto Ulang' : 'Ganti Foto'"></span>
            </button>
        </div>

        {{-- Pesan error (dari validasi server maupun dari browser) --}}
        <div role="alert" x-show="galat" @unless ($errors->has('foto_tanaman')) x-cloak @endunless
             class="flex items-start gap-2 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
            <i data-lucide="circle-alert" class="w-4 h-4 mt-0.5 shrink-0"></i>
            <span x-text="galat">{{ $errors->first('foto_tanaman') }}</span>
        </div>

        {{-- Input file asli. Input pertama ikut terkirim saat form dipakai tanpa JS,
             input kedua membuka kamera bawaan HP kalau WebRTC tidak tersedia. --}}
        <input id="foto_tanaman" x-ref="input" type="file" name="foto_tanaman"
               accept="image/jpeg,image/png,image/webp" required class="sr-only"
               @change="pilihBerkas($event.target.files[0])">
        <input x-ref="inputKamera" type="file" accept="image/*" capture="environment" class="hidden" hidden
               @change="pilihBerkas($event.target.files[0]); $event.target.value = ''">

        <div class="flex flex-wrap gap-3 pt-1">
            <button type="submit" :disabled="sedangProses || menyiapkan"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold shadow-sm transition disabled:opacity-60 disabled:cursor-not-allowed">
                <span x-show="!sedangProses" class="inline-flex"><i data-lucide="scan-search" class="w-4 h-4"></i></span>
                <svg x-cloak x-show="sedangProses" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle>
                    <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                </svg>
                <span x-text="sedangProses ? 'Menganalisis…' : 'Analisis Sekarang'">Analisis Sekarang</span>
            </button>
            @if ($batalUrl)
                <a href="{{ $batalUrl }}" x-show="!sedangProses"
                   class="inline-flex items-center px-6 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold">
                    Batal
                </a>
            @endif
        </div>
    </form>
</div>

@once
    @push('styles')
        <style>
            .scan-overlay { background: rgba(4, 28, 16, .35); }
            .scan-grid {
                position: absolute; inset: 0;
                background-image:
                    linear-gradient(rgba(143, 209, 166, .14) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(143, 209, 166, .14) 1px, transparent 1px);
                background-size: 28px 28px;
            }
            .scan-garis {
                position: absolute; left: 0; right: 0; height: 3px;
                background: linear-gradient(90deg, transparent, #8fd1a6 20%, #dcefe2 50%, #8fd1a6 80%, transparent);
                box-shadow: 0 0 16px 4px rgba(143, 209, 166, .55), 0 0 60px 18px rgba(143, 209, 166, .18);
                animation: scan-sapu 2.4s ease-in-out infinite alternate;
            }
            .scan-titik {
                position: absolute; width: 14px; height: 14px; margin: -7px 0 0 -7px;
                border-radius: 9999px; border: 2px solid #8fd1a6;
                animation: scan-denyut 2.1s ease-out infinite;
            }
            .scan-sudut { position: absolute; width: 28px; height: 28px; border-color: #8fd1a6; }
            @keyframes scan-sapu { from { top: 3%; } to { top: 97%; } }
            @keyframes scan-denyut {
                0%   { transform: scale(.4); opacity: 0; }
                30%  { opacity: 1; }
                100% { transform: scale(2.4); opacity: 0; }
            }
            @media (prefers-reduced-motion: reduce) {
                .scan-garis { animation: none; top: 50%; }
                .scan-titik { animation: none; opacity: .8; }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="{{ asset('assets/js/scanner-penyakit.js') }}"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"
                integrity="sha384-9Ax3MmS9AClxJyd5/zafcXXjxmwFhZCdsT6HJoJjarvCaAkJlk5QDzjLJm+Wdx5F"
                crossorigin="anonymous"></script>
    @endpush
@endonce
