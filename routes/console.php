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
