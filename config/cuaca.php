<?php

/*
| Pengaturan integrasi cuaca untuk lahan petani (lihat App\Services\WeatherService).
| Kunci API OpenWeatherMap ada di config/services.php.
*/

return [

    // Lama respons API cuaca disimpan di cache, dalam detik. Default 3 jam:
    // cukup segar untuk keputusan harian di lahan dan hemat kuota API.
    'cache_detik' => (int) env('CUACA_CACHE_DETIK', 3 * 60 * 60),

    // Koordinat dibulatkan sebelum dipakai sebagai kunci cache dan dikirim
    // ke API. 2 desimal kira-kira 1,1 km, jadi lahan yang berdekatan memakai
    // satu data cuaca yang sama (satu panggilan API, bukan satu per lahan).
    'presisi_koordinat' => 2,

    // Jadwal pembaruan otomatis tabel weather_data (command cuaca:perbarui)
    'jam_pembaruan' => env('CUACA_JAM_PEMBARUAN', '06:00'),
    'zona_waktu' => 'Asia/Jakarta',

    // Nama antrean untuk job pembaruan cuaca
    'antrian' => env('CUACA_ANTRIAN', 'default'),

    // Batas job per menit. Satu job = 2 panggilan OpenWeatherMap (cuaca saat
    // ini + prakiraan), sedangkan paket gratis dibatasi 60 panggilan/menit.
    'job_per_menit' => (int) env('CUACA_JOB_PER_MENIT', 25),

    // Batas waktu satu request ke API cuaca (detik)
    'timeout' => (int) env('CUACA_TIMEOUT', 8),

    // Setelah provider gagal (timeout, error server, kuota habis), provider
    // itu tidak dipanggil lagi selama sekian detik. Halaman tetap cepat
    // karena langsung memakai data cadangan, bukan menunggu timeout berulang.
    'jeda_gangguan_detik' => 300,

    'openweathermap' => [
        'base_url' => env('OPENWEATHER_BASE_URL', 'https://api.openweathermap.org/data/2.5'),
    ],

    // Open-Meteo tidak butuh API key. Dipakai kalau OPENWEATHER_API_KEY
    // kosong, atau sebagai cadangan saat OpenWeatherMap gagal.
    'open_meteo' => [
        'aktif' => (bool) env('CUACA_CADANGAN_OPEN_METEO', true),
        'base_url' => 'https://api.open-meteo.com/v1',
    ],

];
