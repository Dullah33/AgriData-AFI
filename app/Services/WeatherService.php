<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherService
{
    /**
     * Ambil data cuaca berdasarkan koordinat
     * Hasil di-cache selama 1 jam (3600 detik)
     */
    public function getWeather(float $lat, float $lon): array
    {
        $cacheKey = "weather_{$lat}_{$lon}";

        return Cache::remember($cacheKey, 3600, function () use ($lat, $lon) {
            $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current_weather=true&hourly=temperature_2m,relativehumidity_2m,precipitation&timezone=Asia/Jakarta";

            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                throw new \Exception("Gagal mengambil data cuaca dari API.");
            }

            $data = $response->json();
            $current = $data['current_weather'];
            $hourly = $data['hourly'] ?? [];

            // Ambil rata-rata kelembaban & curah hujan dari 24 jam terakhir
            $humidity = $hourly['relativehumidity_2m'] ? round(array_sum(array_slice($hourly['relativehumidity_2m'], 0, 24)) / 24, 2) : 0;
            $precipitation = $hourly['precipitation'] ? round(array_sum(array_slice($hourly['precipitation'], 0, 24)), 2) : 0;

            return [
                'suhu'          => (float) $current['temperature'],
                'kecepatan_angin' => (float) $current['windspeed'],
                'kelembaban'    => $humidity,
                'curah_hujan'   => $precipitation,
                'kondisi_cuaca' => $this->interpretWeatherCode($current['weathercode']),
                'raw_data'      => $data, // Simpan untuk analisis forecast nanti
            ];
        });
    }

    /**
     * Terjemahkan kode cuaca WMO ke bahasa manusia
     */
    private function interpretWeatherCode(int $code): string
    {
        $codes = [
            0 => 'Cerah', 1 => 'Cerah Berawan', 2 => 'Berawan Sebagian', 3 => 'Berawan',
            45 => 'Berkabut', 48 => 'Kabut Tebal', 51 => 'Hujan Gerimis', 53 => 'Hujan Gerimis Sedang',
            55 => 'Hujan Gerimis Lebat', 61 => 'Hujan Ringan', 63 => 'Hujan Sedang',
            65 => 'Hujan Lebat', 71 => 'Salju Ringan', 73 => 'Salju Sedang', 75 => 'Salju Lebat',
            95 => 'Hujan Badai', 96 => 'Hujan Badai dengan Hail', 99 => 'Hujan Badai Hebat'
        ];

        return $codes[$code] ?? 'Tidak Diketahui';
    }
}