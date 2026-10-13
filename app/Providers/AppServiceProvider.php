<?php

namespace App\Providers;

use App\View\Composers\NavigasiComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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

        // Deteksi N+1: relasi yang dimuat lazy di dalam perulangan. Saat test
        // langsung melempar exception (test gagal); di environment non-production
        // lain cukup dicatat ke log supaya tidak pernah memutus halaman pengguna.
        Model::preventLazyLoading(! $this->app->isProduction());

        if (! $this->app->runningUnitTests()) {
            Model::handleLazyLoadingViolationUsing(function (Model $model, string $relasi) {
                Log::warning('N+1 query: relasi '.$model::class."::{$relasi} dimuat lazy di dalam perulangan.");
            });
        }

        // Angka badge di sidebar & navbar dihitung sekali per request
        View::composer(['partials.sidebar', 'partials.navbar'], NavigasiComposer::class);

        $this->aturRateLimiter();
    }

    private function aturRateLimiter(): void
    {
        // Login (web & API): 5 percobaan per menit per kombinasi email + IP,
        // untuk menghambat tebak-tebakan password.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by(Str::lower((string) $request->input('email')).'|'.$request->ip())
                ->response(function (Request $request, array $headers) {
                    $pesan = 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.';

                    return $request->is('api/*') || $request->expectsJson()
                        ? response()->json(['message' => $pesan], 429, $headers)
                        : back()->withErrors(['email' => $pesan])->onlyInput('email');
                });
        });

        // Batas job pembaruan cuaca per menit, supaya tidak melewati kuota
        // paket gratis OpenWeatherMap (60 panggilan/menit).
        RateLimiter::for('cuaca-api', fn () => Limit::perMinute((int) config('cuaca.job_per_menit', 25)));
    }
}
