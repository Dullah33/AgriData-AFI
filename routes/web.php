<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ArtikelController;
use App\Http\Controllers\Admin\PetaniController;
use App\Http\Controllers\Admin\UlasanController;
use App\Http\Controllers\Petani\ProdukPanenController;
use App\Http\Controllers\Petani\PesananController;
use App\Http\Controllers\Petani\LahanController;
use App\Http\Controllers\Petani\KunjunganPenyuluhController;
use App\Http\Controllers\User\MarketplaceController;
use App\Http\Controllers\Admin\PenyuluhController;
use App\Http\Controllers\Penyuluh\WilayahBinaanController;
use App\Http\Controllers\Penyuluh\KunjunganController;
use App\Http\Controllers\Penyuluh\PelatihanController;
use App\Http\Controllers\Penyuluh\LaporanBulananController;

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
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
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
        Route::get('ulasan', [UlasanController::class, 'index'])->name('ulasan.index');
        Route::post('ulasan/{ulasan}/sembunyikan', [UlasanController::class, 'sembunyikan'])->name('ulasan.sembunyikan');
        Route::post('ulasan/{ulasan}/aktifkan', [UlasanController::class, 'aktifkan'])->name('ulasan.aktifkan');

        // Manajemen Penyuluh
        Route::resource('penyuluh', PenyuluhController::class);
        Route::post('penyuluh/{penyuluh}/nonaktifkan', [PenyuluhController::class, 'nonaktifkan'])->name('penyuluh.nonaktifkan');
        Route::post('penyuluh/{penyuluh}/aktifkan', [PenyuluhController::class, 'aktifkan'])->name('penyuluh.aktifkan');
    });

    // ============================================
    // PETANI ROUTES (Role: Petani)
    // ============================================
    Route::middleware('role:petani')->prefix('petani')->name('petani.')->group(function () {
        // Profil & Lahan Saya
        Route::resource('lahan', LahanController::class)->except(['show']);

        // Kunjungan Penyuluh (read-only)
        Route::get('kunjungan-penyuluh', [KunjunganPenyuluhController::class, 'index'])->name('kunjungan-penyuluh.index');

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
    });

    // ============================================
    // PENYULUH ROUTES (Role: Penyuluh)
    // ============================================
    Route::middleware('role:penyuluh')->prefix('penyuluh')->name('penyuluh.')->group(function () {
        // Wilayah Binaan
        Route::get('wilayah-binaan', [WilayahBinaanController::class, 'index'])->name('wilayah-binaan.index');

        // Jadwal & Laporan Kunjungan
        Route::get('kunjungan', [KunjunganController::class, 'index'])->name('kunjungan.index');
        Route::get('kunjungan/create', [KunjunganController::class, 'create'])->name('kunjungan.create');
        Route::post('kunjungan', [KunjunganController::class, 'store'])->name('kunjungan.store');
        Route::get('kunjungan/{kunjungan}/laporkan', [KunjunganController::class, 'laporkan'])->name('kunjungan.laporkan');
        Route::post('kunjungan/{kunjungan}/laporkan', [KunjunganController::class, 'simpanLaporan'])->name('kunjungan.simpan-laporan');
        Route::post('kunjungan/{kunjungan}/batalkan', [KunjunganController::class, 'batalkan'])->name('kunjungan.batalkan');

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