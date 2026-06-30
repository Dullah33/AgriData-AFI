<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;

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
    
    // Satu route /dashboard untuk semua role, view-nya beda-beda
    // tergantung role user (lihat DashboardController)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    /* 
     * PENAMBAHAN ROUTE FITUR LAINNYA SESUAI KEBUTUHAN SISTEM:
     * Route::get('/cuaca', ...);        // Sistem kamu
     * Route::get('/penyakit', ...);     // Sistem teman 1
     * Route::get('/lahan', ...);        // Sistem teman 2
     */
});