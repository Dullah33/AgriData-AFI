<?php

namespace App\Services;

use App\Exceptions\CuacaTidakTersediaException;
use App\Models\Lahan;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;
use UnexpectedValueException;

/**
 * Data cuaca real-time untuk lahan petani.
 *
 * Sumber utama OpenWeatherMap (cuaca saat ini + prakiraan 5 hari), dengan
 * cadangan Open-Meteo yang tidak butuh API key. Respons kedua provider
 * dinormalisasi ke format yang sama, lalu disimpan di cache selama 3 jam
 * per koordinat, jadi membuka halaman berkali-kali tidak menghabiskan kuota.
 *
 * Format hasil: suhu (°C), terasa_seperti, kelembaban (%), kecepatan_angin
 * (km/jam), curah_hujan (mm, total 24 jam ke depan), peluang_hujan (%),
 * kondisi_cuaca, ikon (nama ikon Lucide), lokasi, latitude, longitude,
 * prakiraan (5 hari), sumber, diambil_at.
 */
class WeatherService
{
    public const OPENWEATHERMAP = 'openweathermap';

    public const OPEN_METEO = 'open-meteo';

    /**
     * Cuaca di sebuah koordinat. Kalau masih ada di cache langsung dipakai,
     * kalau tidak baru memanggil API dan hasilnya disimpan selama 3 jam.
     *
     * @throws CuacaTidakTersediaException
     */
    public function getWeather(float $lat, float $lon): array
    {
        [$lat, $lon] = $this->bulatkan($lat, $lon);

        return Cache::remember($this->kunciCache($lat, $lon), $this->lamaCache(), fn () => $this->ambilDariApi($lat, $lon));
    }

    /**
     * Ambil ulang dari API tanpa melihat cache, lalu timpa isi cache. Dipakai
     * job pembaruan pagi, supaya halaman pertama yang dibuka hari itu sudah
     * mendapat data segar tanpa menunggu API.
     *
     * @throws CuacaTidakTersediaException
     */
    public function refresh(float $lat, float $lon): array
    {
        [$lat, $lon] = $this->bulatkan($lat, $lon);

        $cuaca = $this->ambilDariApi($lat, $lon);
        Cache::put($this->kunciCache($lat, $lon), $cuaca, $this->lamaCache());

        return $cuaca;
    }

    /**
     * Cuaca untuk satu lahan. Kalau semua provider sedang gagal, dipakai data
     * terakhir dari tabel weather_data (ditandai 'arsip' => true) supaya
     * petani tetap melihat kondisi terakhir. Null kalau lahan belum punya
     * koordinat, atau belum pernah punya data sama sekali.
     */
    public function untukLahan(Lahan $lahan): ?array
    {
        $titik = $lahan->koordinatCuaca();

        if (! $titik) {
            return null;
        }

        try {
            return ['arsip' => false] + $this->getWeather(...$titik);
        } catch (CuacaTidakTersediaException) {
            $arsip = $lahan->weatherData?->weather_data;

            return $arsip ? ['arsip' => true] + $arsip : null;
        }
    }

    /**
     * Koordinat dibulatkan (default 2 desimal, sekitar 1,1 km). Lahan yang
     * hasil pembulatannya sama memakai satu data cuaca dan satu entri cache.
     */
    public function bulatkan(float $lat, float $lon): array
    {
        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            throw new InvalidArgumentException("Koordinat tidak valid: {$lat}, {$lon}");
        }

        $presisi = (int) config('cuaca.presisi_koordinat', 2);

        return [round($lat, $presisi), round($lon, $presisi)];
    }

    public function kunciCache(float $lat, float $lon): string
    {
        $presisi = (int) config('cuaca.presisi_koordinat', 2);

        return 'cuaca:v1:'.number_format($lat, $presisi, '.', '').':'.number_format($lon, $presisi, '.', '');
    }

    /**
     * Saran perawatan singkat berdasarkan cuaca, maksimal tiga butir.
     */
    public function saranPerawatan(array $cuaca): array
    {
        $saran = [];

        if (($cuaca['curah_hujan'] ?? 0) >= 5 || ($cuaca['peluang_hujan'] ?? 0) >= 70) {
            $saran[] = ['ikon' => 'umbrella', 'teks' => 'Hujan diperkirakan turun dalam 24 jam. Tunda pemupukan dan penyemprotan supaya tidak tercuci air hujan.'];
        }

        if (($cuaca['kecepatan_angin'] ?? 0) >= 15) {
            $saran[] = ['ikon' => 'wind', 'teks' => 'Angin cukup kencang. Hindari menyemprot pestisida karena semprotan mudah terbawa angin.'];
        }

        if (($cuaca['kelembaban'] ?? 0) >= 85) {
            $saran[] = ['ikon' => 'droplets', 'teks' => 'Udara sangat lembap dan rawan jamur. Periksa daun bagian bawah dan buang daun yang bercak.'];
        }

        if (($cuaca['suhu'] ?? 0) >= 33) {
            $saran[] = ['ikon' => 'thermometer-sun', 'teks' => 'Cuaca panas. Siram tanaman pagi atau sore hari untuk mengurangi penguapan.'];
        }

        if (! $saran) {
            $saran[] = ['ikon' => 'sprout', 'teks' => 'Cuaca mendukung untuk perawatan rutin seperti penyiangan dan pemupukan.'];
        }

        return array_slice($saran, 0, 3);
    }

    /**
     * Coba provider satu per satu. Provider yang baru saja gagal dilewati
     * selama beberapa menit (config cuaca.jeda_gangguan_detik), supaya halaman
     * tidak menunggu timeout yang sama berulang kali saat API sedang down.
     */
    private function ambilDariApi(float $lat, float $lon): array
    {
        $urutan = $this->urutanProvider();

        if (! $urutan) {
            throw new CuacaTidakTersediaException('Belum ada sumber data cuaca yang aktif. Isi OPENWEATHER_API_KEY atau aktifkan Open-Meteo.');
        }

        foreach ($urutan as $provider) {
            $kunciGangguan = "cuaca:gangguan:{$provider}";

            if (Cache::has($kunciGangguan)) {
                continue;
            }

            try {
                return match ($provider) {
                    self::OPENWEATHERMAP => $this->dariOpenWeatherMap($lat, $lon),
                    self::OPEN_METEO => $this->dariOpenMeteo($lat, $lon),
                };
            } catch (ConnectionException|RequestException|UnexpectedValueException $e) {
                Cache::put($kunciGangguan, true, (int) config('cuaca.jeda_gangguan_detik', 300));

                // Pesan error HTTP bisa memuat URL lengkap beserta appid, jadi
                // disensor dulu sebelum masuk log. Exception aslinya sengaja
                // tidak diteruskan sebagai "previous" karena alasan yang sama.
                Log::warning("Cuaca: gagal mengambil data dari {$provider}", [
                    'koordinat' => "{$lat},{$lon}",
                    'pesan' => $this->sensor($e->getMessage()),
                ]);
            }
        }

        throw new CuacaTidakTersediaException;
    }

    private function urutanProvider(): array
    {
        $urutan = [];

        if (filled(config('services.openweathermap.key'))) {
            $urutan[] = self::OPENWEATHERMAP;
        }

        if (config('cuaca.open_meteo.aktif', true)) {
            $urutan[] = self::OPEN_METEO;
        }

        return $urutan;
    }

    /**
     * Request dicoba ulang sekali kalau koneksi putus atau server error (5xx).
     * Error 4xx (key salah, kuota habis) tidak diulang karena hasilnya sama.
     */
    private function http(string $baseUrl): PendingRequest
    {
        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->connectTimeout(4)
            ->timeout((int) config('cuaca.timeout', 8))
            ->retry(2, 300, fn (Throwable $e) => $e instanceof ConnectionException
                || ($e instanceof RequestException && $e->response->serverError()));
    }

    /**
     * OpenWeatherMap paket gratis: /weather (cuaca saat ini) dan /forecast
     * (prakiraan 5 hari per 3 jam). Satuan metric: suhu °C, angin m/detik.
     */
    private function dariOpenWeatherMap(float $lat, float $lon): array
    {
        $baseUrl = config('cuaca.openweathermap.base_url');
        $query = [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => config('services.openweathermap.key'),
            'units' => 'metric',
            'lang' => 'id',
        ];

        $sekarang = $this->http($baseUrl)->get('/weather', $query)->throw()->json();
        $prakiraan = $this->http($baseUrl)->get('/forecast', $query)->throw()->json();

        if (! isset($sekarang['main']['temp'], $sekarang['weather'][0]['id']) || ! is_array($prakiraan['list'] ?? null)) {
            throw new UnexpectedValueException('Format respons OpenWeatherMap tidak dikenali.');
        }

        $daftar = collect($prakiraan['list']);
        $offset = (int) ($prakiraan['city']['timezone'] ?? $sekarang['timezone'] ?? 7 * 3600);
        // 8 blok x 3 jam = 24 jam ke depan
        $sehari = $daftar->take(8);
        $malam = str_ends_with($sekarang['weather'][0]['icon'] ?? '', 'n');
        [$kondisi, $ikon] = $this->kondisiOpenWeatherMap((int) $sekarang['weather'][0]['id'], $malam);

        return $this->hasil($lat, $lon, [
            'suhu' => $sekarang['main']['temp'],
            'terasa' => $sekarang['main']['feels_like'] ?? null,
            'kelembaban' => $sekarang['main']['humidity'] ?? 0,
            'angin' => ($sekarang['wind']['speed'] ?? 0) * 3.6,
            'curah_hujan' => $sehari->sum(fn ($blok) => $blok['rain']['3h'] ?? 0),
            'peluang_hujan' => $sehari->max(fn ($blok) => $blok['pop'] ?? 0) * 100,
            'kondisi' => $kondisi,
            'ikon' => $ikon,
            'lokasi' => $sekarang['name'] ?? null,
            'prakiraan' => $this->harianOpenWeatherMap($daftar, $offset),
            'sumber' => self::OPENWEATHERMAP,
        ]);
    }

    /**
     * Ringkas blok 3 jam OpenWeatherMap menjadi prakiraan per hari (waktu lokal).
     */
    private function harianOpenWeatherMap(Collection $daftar, int $offset): array
    {
        return $daftar
            ->groupBy(fn ($blok) => gmdate('Y-m-d', $blok['dt'] + $offset))
            ->take(5)
            ->map(function (Collection $blok, string $tanggal) use ($offset) {
                $hujan = $blok->sum(fn ($b) => $b['rain']['3h'] ?? 0);

                // Kondisi yang mewakili hari itu: blok paling basah kalau ada
                // hujan berarti, selain itu blok yang paling dekat jam 12 siang.
                $wakil = $hujan >= 1
                    ? $blok->sortByDesc(fn ($b) => $b['rain']['3h'] ?? 0)->first()
                    : $blok->sortBy(fn ($b) => abs((int) gmdate('G', $b['dt'] + $offset) - 12))->first();
                [$kondisi, $ikon] = $this->kondisiOpenWeatherMap((int) ($wakil['weather'][0]['id'] ?? 800));

                return [
                    'tanggal' => $tanggal,
                    'suhu_min' => round((float) $blok->min(fn ($b) => $b['main']['temp_min'] ?? $b['main']['temp']), 1),
                    'suhu_max' => round((float) $blok->max(fn ($b) => $b['main']['temp_max'] ?? $b['main']['temp']), 1),
                    'curah_hujan' => round($hujan, 1),
                    'peluang_hujan' => (int) round($blok->max(fn ($b) => $b['pop'] ?? 0) * 100),
                    'kondisi_cuaca' => $kondisi,
                    'ikon' => $ikon,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Open-Meteo: satu request untuk cuaca saat ini, hujan per jam 24 jam ke
     * depan, dan prakiraan harian 5 hari. Angin sudah dalam km/jam.
     */
    private function dariOpenMeteo(float $lat, float $lon): array
    {
        $data = $this->http(config('cuaca.open_meteo.base_url'))->get('/forecast', [
            'latitude' => $lat,
            'longitude' => $lon,
            'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,is_day,precipitation,weather_code,wind_speed_10m',
            'hourly' => 'precipitation,precipitation_probability',
            'forecast_hours' => 24,
            'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max',
            'forecast_days' => 5,
            'timezone' => 'auto',
        ])->throw()->json();

        $sekarang = $data['current'] ?? null;
        $harian = $data['daily'] ?? null;

        if (! isset($sekarang['temperature_2m'], $sekarang['weather_code']) || ! is_array($harian['time'] ?? null)) {
            throw new UnexpectedValueException('Format respons Open-Meteo tidak dikenali.');
        }

        [$kondisi, $ikon] = $this->kondisiWmo((int) $sekarang['weather_code'], ($sekarang['is_day'] ?? 1) == 0);

        $prakiraan = collect($harian['time'])->map(function ($tanggal, $i) use ($harian) {
            [$kondisi, $ikon] = $this->kondisiWmo((int) ($harian['weather_code'][$i] ?? 0));

            return [
                'tanggal' => $tanggal,
                'suhu_min' => round((float) ($harian['temperature_2m_min'][$i] ?? 0), 1),
                'suhu_max' => round((float) ($harian['temperature_2m_max'][$i] ?? 0), 1),
                'curah_hujan' => round((float) ($harian['precipitation_sum'][$i] ?? 0), 1),
                'peluang_hujan' => (int) ($harian['precipitation_probability_max'][$i] ?? 0),
                'kondisi_cuaca' => $kondisi,
                'ikon' => $ikon,
            ];
        })->all();

        return $this->hasil($lat, $lon, [
            'suhu' => $sekarang['temperature_2m'],
            'terasa' => $sekarang['apparent_temperature'] ?? null,
            'kelembaban' => $sekarang['relative_humidity_2m'] ?? 0,
            'angin' => $sekarang['wind_speed_10m'] ?? 0,
            'curah_hujan' => collect($data['hourly']['precipitation'] ?? [])->sum(),
            'peluang_hujan' => collect($data['hourly']['precipitation_probability'] ?? [])->max() ?? 0,
            'kondisi' => $kondisi,
            'ikon' => $ikon,
            'lokasi' => null,
            'prakiraan' => $prakiraan,
            'sumber' => self::OPEN_METEO,
        ]);
    }

    private function hasil(float $lat, float $lon, array $data): array
    {
        return [
            'suhu' => round((float) $data['suhu'], 1),
            'terasa_seperti' => $data['terasa'] !== null ? round((float) $data['terasa'], 1) : null,
            'kelembaban' => (int) round((float) $data['kelembaban']),
            'kecepatan_angin' => round((float) $data['angin'], 1),
            'curah_hujan' => round((float) $data['curah_hujan'], 1),
            'peluang_hujan' => (int) round((float) $data['peluang_hujan']),
            'kondisi_cuaca' => $data['kondisi'],
            'ikon' => $data['ikon'],
            'lokasi' => $data['lokasi'],
            'latitude' => $lat,
            'longitude' => $lon,
            'prakiraan' => $data['prakiraan'],
            'sumber' => $data['sumber'],
            'diambil_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Kode kondisi OpenWeatherMap ke label bahasa Indonesia dan ikon Lucide.
     * https://openweathermap.org/weather-conditions
     */
    private function kondisiOpenWeatherMap(int $id, bool $malam = false): array
    {
        [$label, $ikon] = match (true) {
            $id >= 210 && $id <= 221 => ['Badai Petir', 'cloud-lightning'],
            $id >= 200 && $id < 300 => ['Hujan Badai', 'cloud-lightning'],
            $id >= 300 && $id < 400 => ['Gerimis', 'cloud-drizzle'],
            $id === 500 => ['Hujan Ringan', 'cloud-rain'],
            $id === 501 => ['Hujan Sedang', 'cloud-rain'],
            $id >= 502 && $id <= 504 => ['Hujan Lebat', 'cloud-rain-wind'],
            $id === 511 => ['Hujan Es', 'cloud-hail'],
            $id >= 520 && $id < 600 => ['Hujan Lokal', 'cloud-rain'],
            $id >= 600 && $id < 700 => ['Salju', 'snowflake'],
            $id === 701 => ['Berkabut', 'cloud-fog'],
            $id === 741 => ['Kabut Tebal', 'cloud-fog'],
            $id === 762 => ['Abu Vulkanik', 'haze'],
            $id === 771 => ['Angin Kencang', 'wind'],
            $id === 781 => ['Puting Beliung', 'tornado'],
            $id >= 700 && $id < 800 => ['Udara Berkabut', 'haze'],
            $id === 800 => ['Cerah', 'sun'],
            $id === 801 => ['Cerah Berawan', 'cloud-sun'],
            $id === 802 => ['Berawan Sebagian', 'cloud-sun'],
            $id === 803 => ['Berawan', 'cloud'],
            $id === 804 => ['Mendung', 'cloudy'],
            default => ['Tidak Diketahui', 'cloud'],
        };

        return [$label, $this->ikonWaktu($ikon, $malam)];
    }

    /**
     * Kode cuaca WMO (dipakai Open-Meteo) ke label dan ikon.
     * https://open-meteo.com/en/docs#weather_variable_documentation
     */
    private function kondisiWmo(int $kode, bool $malam = false): array
    {
        [$label, $ikon] = match ($kode) {
            0 => ['Cerah', 'sun'],
            1 => ['Cerah Berawan', 'cloud-sun'],
            2 => ['Berawan Sebagian', 'cloud-sun'],
            3 => ['Mendung', 'cloudy'],
            45 => ['Berkabut', 'cloud-fog'],
            48 => ['Kabut Tebal', 'cloud-fog'],
            51, 53, 56 => ['Gerimis', 'cloud-drizzle'],
            55, 57 => ['Gerimis Lebat', 'cloud-drizzle'],
            61, 66 => ['Hujan Ringan', 'cloud-rain'],
            63 => ['Hujan Sedang', 'cloud-rain'],
            65, 67 => ['Hujan Lebat', 'cloud-rain-wind'],
            71, 73, 75, 77, 85, 86 => ['Salju', 'snowflake'],
            80, 81 => ['Hujan Lokal', 'cloud-rain'],
            82 => ['Hujan Lokal Lebat', 'cloud-rain-wind'],
            95 => ['Hujan Badai', 'cloud-lightning'],
            96, 99 => ['Hujan Badai dan Es', 'cloud-lightning'],
            default => ['Tidak Diketahui', 'cloud'],
        };

        return [$label, $this->ikonWaktu($ikon, $malam)];
    }

    private function ikonWaktu(string $ikon, bool $malam): string
    {
        if (! $malam) {
            return $ikon;
        }

        return match ($ikon) {
            'sun' => 'moon',
            'cloud-sun' => 'cloud-moon',
            default => $ikon,
        };
    }

    private function lamaCache(): int
    {
        return (int) config('cuaca.cache_detik', 3 * 60 * 60);
    }

    private function sensor(string $pesan): string
    {
        $key = (string) config('services.openweathermap.key');
        $pesan = $key !== '' ? str_replace($key, '***', $pesan) : $pesan;

        return preg_replace('/appid=[^&\s]+/i', 'appid=***', $pesan);
    }
}
