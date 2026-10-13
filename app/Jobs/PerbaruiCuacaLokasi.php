<?php

namespace App\Jobs;

use App\Models\Lahan;
use App\Models\WeatherData;
use App\Services\WeatherService;
use DateTimeInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Ambil cuaca terbaru untuk satu titik lokasi, lalu simpan ke weather_data
 * untuk setiap lahan di titik itu. Lahan yang berdekatan sudah dikelompokkan
 * oleh command cuaca:perbarui, jadi satu job cukup satu kali memanggil API.
 */
class PerbaruiCuacaLokasi implements ShouldQueue
{
    use Batchable, Queueable;

    // Job yang ditahan rate limiter dikembalikan ke antrean dan itu dihitung
    // sebagai percobaan. Karena itu batasnya memakai waktu (retryUntil) dan
    // jumlah error sungguhan (maxExceptions), bukan jumlah percobaan.
    public int $maxExceptions = 4;

    public int $timeout = 60;

    // Jeda sebelum mencoba lagi setelah gagal. Percobaan pertama sengaja
    // lebih lama dari jeda gangguan provider (5 menit) di WeatherService.
    public array $backoff = [360, 900, 1800];

    public function __construct(
        public float $latitude,
        public float $longitude,
        public array $lahanIds,
    ) {
        $this->onQueue(config('cuaca.antrian'));
    }

    public function middleware(): array
    {
        return [new SkipIfBatchCancelled, new RateLimited('cuaca-api')];
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addHours(3);
    }

    public function handle(WeatherService $cuaca): void
    {
        $data = $cuaca->refresh($this->latitude, $this->longitude);

        Lahan::with('petaniProfile.wilayah')->whereKey($this->lahanIds)->get()
            ->each(fn (Lahan $lahan) => WeatherData::simpanUntukLahan($lahan, $data));
    }

    public function failed(?Throwable $e): void
    {
        Log::warning('Cuaca: pembaruan lokasi gagal setelah beberapa percobaan', [
            'koordinat' => "{$this->latitude},{$this->longitude}",
            'lahan' => $this->lahanIds,
            'pesan' => $e?->getMessage(),
        ]);
    }
}
