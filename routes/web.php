<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\BudidayaController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ArtikelController;
use App\Http\Controllers\Admin\PetaniController;
use App\Http\Controllers\Admin\UlasanController as AdminUlasanController;
use App\Http\Controllers\Petani\ProdukPanenController;
use App\Http\Controllers\Petani\PesananController;
use App\Http\Controllers\Petani\LahanController;
use App\Http\Controllers\Petani\KunjunganPenyuluhController;
use App\Http\Controllers\User\MarketplaceController;
use App\Http\Controllers\User\UlasanController as UserUlasanController;
use App\Http\Controllers\ArtikelPublicController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PetaLahanController;
use App\Http\Controllers\Admin\PemetaanPenyakitController;
use App\Http\Controllers\User\ProfilPetaniController;
use App\Http\Controllers\Admin\PenyuluhController;
use App\Http\Controllers\Penyuluh\WilayahBinaanController;
use App\Http\Controllers\Penyuluh\KunjunganController;
use App\Http\Controllers\Penyuluh\PelatihanController;
use App\Http\Controllers\Penyuluh\LaporanBulananController;
use App\Http\Controllers\Penyuluh\DeteksiPenyakitController as PenyuluhDeteksiPenyakitController;
use App\Http\Controllers\Petani\DeteksiPenyakitController as PetaniDeteksiPenyakitController;
use App\Http\Controllers\User\DeteksiPenyakitController as UserDeteksiPenyakitController;

// Landing page
Route::get('/', fn() => view('landing'))->name('landing');

// Guest
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => view('auth.login'))->name('login');
    Route::post('/login', [LoginController::class, 'authenticate']);
    Route::get('/register', fn() => view('auth.register'))->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

// Protected (semua role)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pengaturan Akun — dipakai bersama oleh keempat role
    Route::get('/akun', [AccountController::class, 'edit'])->name('akun.edit');
    Route::put('/akun', [AccountController::class, 'update'])->name('akun.update');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Artikel Pertanian (baca) — dipakai bersama Petani & User
    Route::get('/artikel', [ArtikelPublicController::class, 'index'])->name('artikel.index');
    Route::get('/artikel/{artikel:slug}', [ArtikelPublicController::class, 'show'])->name('artikel.show');
    
    // ============================================
    // WEATHER FEATURE 
    // ============================================
    Route::get('/cuaca', [WeatherController::class, 'index'])->name('cuaca.index');
    
    // API: Cascading Location (EMSIFA)
    Route::get('/api/provinces', [WeatherController::class, 'getProvinces'])->name('api.provinces');
    Route::get('/api/regencies/{provCode}', [WeatherController::class, 'getRegencies'])->name('api.regencies');
    Route::get('/api/districts/{regCode}', [WeatherController::class, 'getDistricts'])->name('api.districts');
    
    // API: Analisis Cuaca
    Route::get('/api/cuaca/recommendations/{provinsi}/{kabupaten}/{kecamatan}', 
        [WeatherController::class, 'getRecommendations'])->name('api.cuaca.recommendations');
    Route::post('/api/cuaca/analyze', [WeatherController::class, 'analyze'])->name('api.cuaca.analyze');
    
    // API: Data Tanaman
    Route::get('/api/tanaman', [WeatherController::class, 'getPlants'])->name('api.tanaman');
    Route::get('/api/tanaman/{id}/detail', [WeatherController::class, 'getPlantDetail'])->name('api.tanaman.detail');

    // Halaman list tanaman
    Route::get('/budidaya', [BudidayaController::class, 'index'])->name('budidaya.index');
    Route::get('/budidaya/{id}', [BudidayaController::class, 'show'])->name('budidaya.show');
    Route::get('/api/budidaya/{id}', [BudidayaController::class, 'apiShow'])->name('api.budidaya.show');

    // ============================================
    // ADMIN ROUTES (Role: Admin)
    // ============================================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('artikel', ArtikelController::class)->except(['show']);

        // Verifikasi akun petani
        Route::get('petani', [PetaniController::class, 'index'])->name('petani.index');
        Route::get('petani/{petani}', [PetaniController::class, 'show'])->name('petani.show');
        Route::post('petani/{petani}/verifikasi', [PetaniController::class, 'verifikasi'])->name('petani.verifikasi');
        Route::post('petani/{petani}/batalkan-verifikasi', [PetaniController::class, 'batalkanVerifikasi'])->name('petani.batalkan-verifikasi');

        // Moderasi ulasan
        Route::get('ulasan', [AdminUlasanController::class, 'index'])->name('ulasan.index');
        Route::post('ulasan/{ulasan}/sembunyikan', [AdminUlasanController::class, 'sembunyikan'])->name('ulasan.sembunyikan');
        Route::post('ulasan/{ulasan}/aktifkan', [AdminUlasanController::class, 'aktifkan'])->name('ulasan.aktifkan');

        // Manajemen Penyuluh
        Route::resource('penyuluh', PenyuluhController::class);
        Route::post('penyuluh/{penyuluh}/nonaktifkan', [PenyuluhController::class, 'nonaktifkan'])->name('penyuluh.nonaktifkan');
        Route::post('penyuluh/{penyuluh}/aktifkan', [PenyuluhController::class, 'aktifkan'])->name('penyuluh.aktifkan');

        // Laporan & Ekspor Data
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/petani/export', [LaporanController::class, 'exportPetani'])->name('laporan.petani.export');
        Route::get('laporan/transaksi/export', [LaporanController::class, 'exportTransaksi'])->name('laporan.transaksi.export');

        // Peta Lahan & Wilayah
        Route::get('peta-lahan', [PetaLahanController::class, 'index'])->name('peta-lahan.index');

        // Pemetaan Penyakit
        Route::get('pemetaan-penyakit', [PemetaanPenyakitController::class, 'index'])->name('pemetaan-penyakit.index');
        // Plant Management
        Route::resource('plants', \App\Http\Controllers\Admin\PlantController::class);
    });

    // ============================================
    // PETANI ROUTES (Role: Petani)
    // ============================================
    Route::middleware('role:petani')->prefix('petani')->name('petani.')->group(function () {
        // Profil & Lahan Saya
        Route::resource('lahan', LahanController::class)->except(['show']);

        // Kunjungan Penyuluh (read-only)
        Route::get('kunjungan-penyuluh', [KunjunganPenyuluhController::class, 'index'])->name('kunjungan-penyuluh.index');

        // AI Scanner Penyakit Tanaman & Riwayat Deteksi Penyakit
        Route::get('deteksi-penyakit', [PetaniDeteksiPenyakitController::class, 'index'])->name('deteksi-penyakit.index');
        Route::get('deteksi-penyakit/scan', [PetaniDeteksiPenyakitController::class, 'create'])->name('deteksi-penyakit.create');
        Route::post('deteksi-penyakit/scan', [PetaniDeteksiPenyakitController::class, 'store'])->name('deteksi-penyakit.store');
        Route::get('deteksi-penyakit/{laporan}', [PetaniDeteksiPenyakitController::class, 'show'])->name('deteksi-penyakit.show');

        // Listing produk
        Route::resource('produk', ProdukPanenController::class)->except(['show']);

        // Pesanan masuk
        Route::get('pesanan', [PesananController::class, 'index'])->name('pesanan.index');
        Route::post('pesanan/{transaksi}/konfirmasi', [PesananController::class, 'konfirmasi'])->name('pesanan.konfirmasi');
        Route::post('pesanan/{transaksi}/selesai', [PesananController::class, 'selesai'])->name('pesanan.selesai');
        Route::post('pesanan/{transaksi}/tolak', [PesananController::class, 'tolak'])->name('pesanan.tolak');
    });

    // ============================================
    // USER ROUTES (Role: User)
    // ============================================
    Route::middleware('role:user')->group(function () {
        Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('user.marketplace');
        Route::get('/marketplace/{produk}', [MarketplaceController::class, 'show'])->name('user.marketplace.show');
        Route::post('/marketplace/{produk}/beli', [MarketplaceController::class, 'beli'])->name('user.marketplace.beli');
        Route::get('/pesanan-saya', [MarketplaceController::class, 'pesananSaya'])->name('user.pesanan');
        Route::get('/riwayat-ulasan', [MarketplaceController::class, 'riwayatUlasan'])->name('user.riwayat-ulasan');
        Route::get("/ulasan/{transaksi}/create", [UserUlasanController::class, "create"])->name("user.ulasan.create");
        Route::post("/ulasan/{transaksi}", [UserUlasanController::class, "store"])->name("user.ulasan.store");

        // AI Scanner Penyakit Tanaman (Terbatas)
        Route::get('/ai-scanner', [UserDeteksiPenyakitController::class, 'create'])->name('user.deteksi-penyakit.create');
        Route::post('/ai-scanner', [UserDeteksiPenyakitController::class, 'store'])->name('user.deteksi-penyakit.store');
        Route::get('/ai-scanner/{laporan}', [UserDeteksiPenyakitController::class, 'show'])->name('user.deteksi-penyakit.show');

        // Profil Petani (Lihat)
        Route::get('/profil-petani', [ProfilPetaniController::class, 'index'])->name('user.petani.index');
        Route::get('/profil-petani/{petani}', [ProfilPetaniController::class, 'show'])->name('user.petani.show');
    });

    // ============================================
    // PENYULUH ROUTES (Role: Penyuluh)
    // ============================================
    Route::middleware('role:penyuluh')->prefix('penyuluh')->name('penyuluh.')->group(function () {
        // Wilayah Binaan
        Route::get('wilayah-binaan', [WilayahBinaanController::class, 'index'])->name('wilayah-binaan.index');

        // Jadwal & Laporan Kunjungan
        Route::get('kunjungan', [KunjunganController::class, 'index'])->name('kunjungan.index');
        Route::get('kunjungan/laporan', [KunjunganController::class, 'riwayat'])->name('kunjungan.riwayat');
        Route::get('kunjungan/create', [KunjunganController::class, 'create'])->name('kunjungan.create');
        Route::post('kunjungan', [KunjunganController::class, 'store'])->name('kunjungan.store');
        Route::get('kunjungan/{kunjungan}/laporkan', [KunjunganController::class, 'laporkan'])->name('kunjungan.laporkan');
        Route::post('kunjungan/{kunjungan}/laporkan', [KunjunganController::class, 'simpanLaporan'])->name('kunjungan.simpan-laporan');
        Route::post('kunjungan/{kunjungan}/batalkan', [KunjunganController::class, 'batalkan'])->name('kunjungan.batalkan');

        // Deteksi Penyakit di Wilayah Binaan
        Route::get('deteksi-penyakit', [PenyuluhDeteksiPenyakitController::class, 'index'])->name('deteksi-penyakit.index');
        Route::get('deteksi-penyakit/{laporan}', [PenyuluhDeteksiPenyakitController::class, 'show'])->name('deteksi-penyakit.show');
        Route::post('deteksi-penyakit/{laporan}/tindak-lanjut', [PenyuluhDeteksiPenyakitController::class, 'tindakLanjut'])->name('deteksi-penyakit.tindak-lanjut');

        // Pelatihan Kelompok Tani
        Route::resource('pelatihan', PelatihanController::class)->except(['show']);

        // Laporan Kinerja Bulanan
        Route::get('laporan-bulanan', [LaporanBulananController::class, 'index'])->name('laporan-bulanan.index');
        Route::get('laporan-bulanan/create', [LaporanBulananController::class, 'create'])->name('laporan-bulanan.create');
        Route::post('laporan-bulanan', [LaporanBulananController::class, 'store'])->name('laporan-bulanan.store');
        Route::get('laporan-bulanan/{laporanBulanan}/edit', [LaporanBulananController::class, 'edit'])->name('laporan-bulanan.edit');
        Route::put('laporan-bulanan/{laporanBulanan}', [LaporanBulananController::class, 'update'])->name('laporan-bulanan.update');
        Route::post('laporan-bulanan/{laporanBulanan}/kirim', [LaporanBulananController::class, 'kirim'])->name('laporan-bulanan.kirim');
    });
});