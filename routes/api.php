<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProdukPanenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Autentikasi (publik). Dibatasi supaya tidak bisa dipakai menebak password.
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

// Rute yang diproteksi token (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('produk-panen', ProdukPanenController::class)
        ->missing(fn (Request $request) => response()->json([
            'error'   => 'Resource tidak ditemukan',
            'message' => 'Produk panen dengan ID '.$request->route('produk_panen').' tidak ada di sistem.',
        ], 404));
});
