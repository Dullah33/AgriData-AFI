<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\WeatherController;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Landing Page & Auth)
|--------------------------------------------------------------------------
*/

// Landing page (untuk semua orang)
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Routes untuk GUEST (belum login)
Route::middleware('guest')->group(function () {
    
    // Login Routes
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::post('/login', [LoginController::class, 'authenticate']);
    
    // Register Routes
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');
    
    Route::post('/register', [RegisterController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| 2. PROTECTED ROUTES (Harus Login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Halaman utama fitur cuaca
    Route::get('/cuaca', [WeatherController::class, 'index'])->name('cuaca.index');
    
    // ============================ Prediksi cuaca ===========================
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
    
    /* 
     * PENAMBAHAN ROUTE FITUR LAINNYA SESUAI KEBUTUHAN SISTEM:
     * Route::get('/cuaca', ...);        // Sistem kamu
     * Route::get('/penyakit', ...);     // Sistem teman 1
     * Route::get('/lahan', ...);        // Sistem teman 2
     */
});