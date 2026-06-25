<?php

use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/test', function () {
    return 'Route bekerja!';
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');