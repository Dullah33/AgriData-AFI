<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Marketplace: batalkan pesanan yang lewat batas bayar dan kembalikan stok.
// Di server perlu menjalankan `php artisan schedule:work` (atau cron
// `* * * * * php artisan schedule:run`) supaya jadwal ini aktif.
Schedule::command('pembayaran:kedaluwarsa')->everyFifteenMinutes()->withoutOverlapping();

// Cuaca lahan: setiap pagi cuaca semua lahan diambil ulang dan disimpan ke
// tabel weather_data. Command ini hanya memasukkan job ke antrean; yang
// memanggil API adalah worker (`php artisan queue:work`), jadi jadwal lain
// tidak ikut tertahan menunggu API cuaca.
Schedule::command('cuaca:perbarui')
    ->dailyAt(config('cuaca.jam_pembaruan'))
    ->timezone(config('cuaca.zona_waktu'))
    ->withoutOverlapping()
    ->onOneServer();
