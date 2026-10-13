<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Railway (dan platform hosting sejenis) men-terminate HTTPS di reverse
        // proxy mereka, lalu meneruskan trafik ke aplikasi lewat HTTP biasa.
        // Tanpa ini, Laravel tidak tahu request aslinya HTTPS, sehingga semua
        // URL/form yang di-generate (route(), url(), form action, dst) memakai
        // http:// dan memicu peringatan "not secure" di browser.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Batas job pembaruan cuaca per menit, supaya tidak melewati kuota
        // paket gratis OpenWeatherMap (60 panggilan/menit).
        RateLimiter::for('cuaca-api', fn () => Limit::perMinute((int) config('cuaca.job_per_menit', 25)));
    }
}