<?php

namespace Tests\Feature;

use App\Exceptions\CuacaTidakTersediaException;
use App\Jobs\PerbaruiCuacaLokasi;
use App\Models\Lahan;
use App\Models\PetaniProfile;
use App\Models\Plant;
use App\Models\User;
use App\Models\WeatherData;
use App\Models\Wilayah;
use App\Services\WeatherService;
use Illuminate\Bus\PendingBatch;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Integrasi cuaca lahan: WeatherService (OpenWeatherMap + Open-Meteo),
 * cache 3 jam, job & command pembaruan harian, dan tampilannya.
 * Semua request HTTP dipalsukan, tidak ada panggilan ke API sungguhan.
 */
class CuacaLahanTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'kunci-uji-123';

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Sleep::fake();
        config(['services.openweathermap.key' => self::KEY]);
        // 06.00 WIB
        $this->travelTo(Carbon::parse('2026-10-12 23:00:00', 'UTC'));
    }

    public function test_mengambil_cuaca_dari_openweathermap_dan_menormalkan_data(): void
    {
        $this->palsukanOpenWeatherMap();

        $cuaca = app(WeatherService::class)->getWeather(-7.6823, 110.8391);

        $this->assertSame(27.4, $cuaca['suhu']);
        $this->assertSame(30.4, $cuaca['terasa_seperti']);
        $this->assertSame(84, $cuaca['kelembaban']);
        $this->assertSame(11.5, $cuaca['kecepatan_angin'], 'angin m/detik harus diubah ke km/jam');
        $this->assertSame(4.0, $cuaca['curah_hujan'], 'total hujan 8 blok (24 jam) pertama');
        $this->assertSame(85, $cuaca['peluang_hujan']);
        $this->assertSame('Hujan Ringan', $cuaca['kondisi_cuaca']);
        $this->assertSame('cloud-rain', $cuaca['ikon']);
        $this->assertSame('Sukoharjo', $cuaca['lokasi']);
        $this->assertSame(WeatherService::OPENWEATHERMAP, $cuaca['sumber']);
        $this->assertSame([-7.68, 110.84], [$cuaca['latitude'], $cuaca['longitude']]);

        $this->assertCount(5, $cuaca['prakiraan']);
        $this->assertSame([
            'tanggal' => '2026-10-13', 'suhu_min' => 24.0, 'suhu_max' => 32.0, 'curah_hujan' => 4.0,
            'peluang_hujan' => 85, 'kondisi_cuaca' => 'Hujan Ringan', 'ikon' => 'cloud-rain',
        ], $cuaca['prakiraan'][0]);
        $this->assertSame('2026-10-14', $cuaca['prakiraan'][1]['tanggal']);
        $this->assertSame('Berawan Sebagian', $cuaca['prakiraan'][1]['kondisi_cuaca']);

        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/data/2.5/weather')
            && $r['lat'] == -7.68 && $r['lon'] == 110.84
            && $r['appid'] === self::KEY && $r['units'] === 'metric');
        Http::assertSentCount(2);
    }

    public function test_respons_disimpan_di_cache_selama_tiga_jam(): void
    {
        $this->palsukanOpenWeatherMap();
        $layanan = app(WeatherService::class);

        $layanan->getWeather(-7.6823, 110.8391);
        $layanan->getWeather(-7.6823, 110.8391);
        // Lahan tetangga (beda ratusan meter) memakai entri cache yang sama
        $layanan->getWeather(-7.6801, 110.8449);
        Http::assertSentCount(2);

        $this->travel(2)->hours();
        $this->travel(59)->minutes();
        $layanan->getWeather(-7.6823, 110.8391);
        Http::assertSentCount(2);

        $this->travel(2)->minutes();
        $layanan->getWeather(-7.6823, 110.8391);
        Http::assertSentCount(4);
    }

    public function test_refresh_mengabaikan_cache_lalu_menimpa_isinya(): void
    {
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::sequence()
                ->push($this->owmSekarang())
                ->push($this->owmSekarang(['main' => ['temp' => 30.2]])),
            'api.openweathermap.org/data/2.5/forecast*' => Http::response($this->owmPrakiraan()),
        ]);
        $layanan = app(WeatherService::class);

        $this->assertSame(27.4, $layanan->getWeather(-7.68, 110.84)['suhu']);
        $this->assertSame(30.2, $layanan->refresh(-7.68, 110.84)['suhu']);
        $this->assertSame(30.2, $layanan->getWeather(-7.68, 110.84)['suhu']);
        Http::assertSentCount(4);
    }

    public function test_beralih_ke_open_meteo_saat_openweathermap_gagal(): void
    {
        Log::spy();
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['cod' => 500, 'message' => 'Internal error'], 500),
            'api.open-meteo.com/*' => Http::response($this->openMeteo()),
        ]);

        $cuaca = app(WeatherService::class)->getWeather(-7.68, 110.84);

        $this->assertSame(WeatherService::OPEN_METEO, $cuaca['sumber']);
        $this->assertSame(25.8, $cuaca['suhu']);
        $this->assertSame(91, $cuaca['kelembaban']);
        $this->assertSame(4.7, $cuaca['kecepatan_angin']);
        $this->assertSame(3.5, $cuaca['curah_hujan']);
        $this->assertSame(65, $cuaca['peluang_hujan']);
        $this->assertSame('Mendung', $cuaca['kondisi_cuaca']);
        $this->assertSame('Hujan Ringan', $cuaca['prakiraan'][0]['kondisi_cuaca']);
        $this->assertSame(31.2, $cuaca['prakiraan'][0]['suhu_max']);

        // Server error dicoba ulang sekali sebelum pindah provider
        Http::assertSentCount(3);
        Log::shouldHaveReceived('warning')->withArgs(fn ($pesan) => str_contains($pesan, 'openweathermap'))->once();
    }

    public function test_tanpa_api_key_langsung_memakai_open_meteo(): void
    {
        config(['services.openweathermap.key' => null]);
        Http::fake(['api.open-meteo.com/*' => Http::response($this->openMeteo())]);

        $cuaca = app(WeatherService::class)->getWeather(-7.68, 110.84);

        $this->assertSame(WeatherService::OPEN_METEO, $cuaca['sumber']);
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'openweathermap'));
        Http::assertSent(fn (Request $r) => $r['forecast_hours'] == 24 && $r['timezone'] === 'auto');
    }

    public function test_semua_provider_gagal_tidak_disimpan_di_cache_dan_dijeda_lima_menit(): void
    {
        Http::fake(['*' => Http::response('Bad Gateway', 502)]);
        $layanan = app(WeatherService::class);

        $this->assertGagal(fn () => $layanan->getWeather(-7.68, 110.84));
        $this->assertFalse(Cache::has($layanan->kunciCache(-7.68, 110.84)));
        $terkirim = count(Http::recorded());

        // Selama jeda, provider tidak dipanggil lagi: halaman tidak ikut menunggu timeout
        $this->assertGagal(fn () => $layanan->getWeather(-7.68, 110.84));
        Http::assertSentCount($terkirim);

        $this->travel(6)->minutes();
        $this->assertGagal(fn () => $layanan->getWeather(-7.68, 110.84));
        $this->assertGreaterThan($terkirim, count(Http::recorded()));
    }

    public function test_api_key_disensor_dari_log_saat_koneksi_putus(): void
    {
        Log::spy();
        config(['cuaca.open_meteo.aktif' => false]);
        Http::fake(fn (Request $r) => throw new ConnectionException('cURL error 28: Operation timed out for '.$r->url()));

        $this->assertGagal(fn () => app(WeatherService::class)->getWeather(-7.68, 110.84));

        Log::shouldHaveReceived('warning')->withArgs(function ($pesan, $konteks) {
            return str_contains($konteks['pesan'], 'appid=***') && ! str_contains($konteks['pesan'], self::KEY);
        })->once();
    }

    public function test_lahan_menyimpan_titik_tengah_poligon_sebagai_koordinat(): void
    {
        [$petani, $profil] = $this->buatPetani();

        $lahan = Lahan::create([
            'petani_profile_id' => $profil->id, 'nama_lahan' => 'Sawah Ngadirejo', 'luas_ha' => 0.8,
            'koordinat_poligon' => [[-7.60, 110.80], [-7.70, 110.80], [-7.70, 110.90]],
        ]);
        $this->assertEqualsWithDelta(-7.6666667, $lahan->latitude, 0.0000001);
        $this->assertEqualsWithDelta(110.8333333, $lahan->longitude, 0.0000001);

        $lahan->update(['koordinat_poligon' => [[-7.50, 110.70], [-7.52, 110.70], [-7.52, 110.72], [-7.50, 110.72]]]);
        $this->assertSame([-7.51, 110.71], $lahan->fresh()->koordinatCuaca());

        // Tanpa poligon: pakai titik wilayah tempat petani terdaftar
        $tegalan = Lahan::create(['petani_profile_id' => $profil->id, 'nama_lahan' => 'Tegalan', 'luas_ha' => 0.3]);
        $this->assertNull($tegalan->latitude);
        $this->assertSame([-7.68, 110.84], $tegalan->koordinatCuaca());
    }

    public function test_command_mengelompokkan_lahan_per_lokasi_lalu_memasukkan_ke_antrean(): void
    {
        Bus::fake();
        [, $profil] = $this->buatPetani();
        $a = $this->buatLahan($profil, 'Sawah A', [-7.6823, 110.8391]);
        $b = $this->buatLahan($profil, 'Sawah B', [-7.6801, 110.8449]);
        $c = $this->buatLahan($profil, 'Kebun Wonogiri', [-7.8140, 110.9260]);

        $wilayahRusak = Wilayah::create(['nama_wilayah' => 'Data rusak', 'latitude' => 95, 'longitude' => 110]);
        [, $profilLain] = $this->buatPetani($wilayahRusak);
        Lahan::create(['petani_profile_id' => $profilLain->id, 'nama_lahan' => 'Tanpa titik', 'luas_ha' => 1]);

        $this->artisan('cuaca:perbarui')
            ->expectsOutputToContain('1 lahan dilewati')
            ->expectsOutputToContain('2 lokasi (3 lahan) masuk antrean')
            ->assertSuccessful();

        Bus::assertBatched(function (PendingBatch $batch) use ($a, $b, $c) {
            $lokasi = $batch->jobs->mapWithKeys(fn (PerbaruiCuacaLokasi $job) => ["{$job->latitude},{$job->longitude}" => $job->lahanIds]);

            return $batch->allowsFailures()
                && $lokasi->count() === 2
                && $lokasi['-7.68,110.84'] === [$a->id, $b->id]
                && $lokasi['-7.81,110.93'] === [$c->id];
        });
    }

    public function test_job_menyimpan_cuaca_ke_weather_data_dan_riwayat_harian(): void
    {
        $this->palsukanOpenWeatherMap();
        [, $profil] = $this->buatPetani();
        $a = $this->buatLahan($profil, 'Sawah A', [-7.6823, 110.8391]);
        $b = $this->buatLahan($profil, 'Sawah B', [-7.6801, 110.8449]);

        dispatch(new PerbaruiCuacaLokasi(-7.68, 110.84, [$a->id, $b->id]));

        $this->assertSame(2, WeatherData::count());
        $data = $a->fresh()->weatherData;
        $this->assertSame('Sawah A', $data->nama_lokasi);
        $this->assertSame('Sukoharjo', $data->kabupaten);
        $this->assertEquals(27.4, $data->suhu);
        $this->assertSame('Hujan Ringan', $data->kondisi_cuaca);
        $this->assertSame(WeatherService::OPENWEATHERMAP, $data->sumber);
        $this->assertCount(5, $data->forecast_data);
        // JSON menyimpan 4.0 sebagai 4, jadi dibandingkan nilainya saja
        $this->assertEquals([['tanggal' => '2026-10-13', 'suhu' => 27.4, 'kelembaban' => 84, 'curah_hujan' => 4.0, 'kondisi_cuaca' => 'Hujan Ringan']], $data->historical_data);

        // Job juga mengisi cache, jadi halaman pertama pagi itu tidak memanggil API
        Http::assertSentCount(2);
        app(WeatherService::class)->getWeather(-7.68, 110.84);
        Http::assertSentCount(2);

        // Hari berikutnya menambah satu baris riwayat, dijalankan ulang di hari yang sama tidak
        $this->travel(1)->day();
        dispatch(new PerbaruiCuacaLokasi(-7.68, 110.84, [$a->id]));
        dispatch(new PerbaruiCuacaLokasi(-7.68, 110.84, [$a->id]));
        $this->assertSame(['2026-10-13', '2026-10-14'], array_column($a->fresh()->weatherData->historical_data, 'tanggal'));
        $this->assertSame(2, WeatherData::count());
    }

    public function test_job_memakai_rate_limit_dan_batas_waktu_percobaan(): void
    {
        $job = new PerbaruiCuacaLokasi(-7.68, 110.84, [1]);

        $this->assertSame(['SkipIfBatchCancelled', 'RateLimited'], array_map(fn ($m) => class_basename($m), $job->middleware()));
        $this->assertTrue($job->retryUntil()->greaterThan(now()->addHours(2)));
        $this->assertSame(config('cuaca.antrian'), $job->queue);
    }

    public function test_command_sync_langsung_memperbarui_tanpa_queue(): void
    {
        $this->palsukanOpenWeatherMap();
        [, $profil] = $this->buatPetani();
        $lahan = $this->buatLahan($profil, 'Sawah A', [-7.6823, 110.8391]);

        $this->artisan('cuaca:perbarui', ['--sync' => true])
            ->expectsOutputToContain('1 dari 1 lokasi diperbarui (1 lahan)')
            ->assertSuccessful();

        $this->assertNotNull($lahan->fresh()->weatherData);
    }

    public function test_jadwal_pembaruan_cuaca_setiap_pukul_enam_pagi_wib(): void
    {
        $this->app->make(ConsoleKernel::class)->bootstrap();

        $jadwal = collect($this->app->make(Schedule::class)->events())
            ->first(fn ($event) => str_contains($event->command ?? '', 'cuaca:perbarui'));

        $this->assertNotNull($jadwal, 'cuaca:perbarui belum dijadwalkan');
        $this->assertSame('0 6 * * *', $jadwal->expression);
        $this->assertSame('Asia/Jakarta', $jadwal->timezone);
        $this->assertTrue($jadwal->withoutOverlapping);
        $this->assertTrue($jadwal->onOneServer);
    }

    public function test_halaman_lahan_menampilkan_cuaca_tiap_lahan(): void
    {
        $this->palsukanOpenWeatherMap();
        [$petani, $profil] = $this->buatPetani();
        $this->buatLahan($profil, 'Sawah Ngadirejo', [-7.6823, 110.8391]);
        Lahan::create(['petani_profile_id' => $profil->id, 'nama_lahan' => 'Tegalan Gentan', 'luas_ha' => 0.3]);

        $this->actingAs($petani)->get(route('petani.lahan.index'))
            ->assertOk()
            ->assertSee('Sawah Ngadirejo')
            ->assertSee('27&deg;C', false)
            ->assertSee('Hujan Ringan')
            ->assertSee('Diperbarui 06.00 WIB')
            ->assertSee('OpenWeatherMap')
            ->assertSee('Lokasi cuaca mengikuti titik wilayah');

        // Kedua lahan ada di titik yang sama setelah dibulatkan: cukup satu kali panggil API
        Http::assertSentCount(2);
    }

    public function test_halaman_lahan_memakai_data_terakhir_saat_api_gagal(): void
    {
        Http::fake(['*' => Http::response('Service Unavailable', 503)]);
        [$petani, $profil] = $this->buatPetani();
        $lahan = $this->buatLahan($profil, 'Sawah Ngadirejo', [-7.6823, 110.8391]);
        WeatherData::simpanUntukLahan($lahan, $this->cuacaContoh(['diambil_at' => now()->subHours(5)->toIso8601String()]));

        $this->actingAs($petani)->get(route('petani.lahan.index'))
            ->assertOk()
            ->assertSee('Layanan cuaca sedang tidak bisa dihubungi')
            ->assertSee('5 jam yang lalu')
            ->assertSee('29&deg;C', false);
    }

    public function test_dashboard_petani_menampilkan_cuaca_prakiraan_dan_saran(): void
    {
        $this->palsukanOpenWeatherMap();
        [$petani, $profil] = $this->buatPetani();
        $this->buatLahan($profil, 'Sawah Ngadirejo', [-7.6823, 110.8391]);

        $this->actingAs($petani)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Cuaca di Sawah Ngadirejo')
            ->assertSee('Prakiraan 5 hari')
            ->assertSee('Hari ini')
            ->assertSee('Tunda pemupukan dan penyemprotan');
    }

    public function test_dashboard_petani_tanpa_lahan_menampilkan_ajakan_menambah_lahan(): void
    {
        [$petani] = $this->buatPetani();

        $this->actingAs($petani)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum ada lahan')
            ->assertSee('Tambahkan lahan untuk memantau cuaca');
        Http::assertNothingSent();
    }

    public function test_rekomendasi_tanam_di_halaman_cuaca_memakai_data_baru(): void
    {
        $this->palsukanOpenWeatherMap();
        Http::fake(['nominatim.openstreetmap.org/*' => Http::response([['lat' => '-7.6823', 'lon' => '110.8391', 'display_name' => 'Baki, Sukoharjo']])]);
        Plant::create([
            'kode' => 'CBR', 'nama' => 'Cabai rawit', 'min_suhu' => 24, 'max_suhu' => 30,
            'min_kelembaban' => 70, 'max_kelembaban' => 90, 'musim' => 'Musim hujan',
        ]);
        [$petani] = $this->buatPetani();

        $this->actingAs($petani)->getJson('/api/cuaca/recommendations/Jawa%20Tengah/Sukoharjo/Baki')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('weather.suhu', 27.4)
            ->assertJsonPath('weather.kelembaban', 84)
            ->assertJsonPath('recommendations.0.nama', 'Cabai rawit');
    }

    // ---------------------------------------------------------------

    private function assertGagal(callable $aksi): void
    {
        try {
            $aksi();
            $this->fail('Seharusnya melempar CuacaTidakTersediaException');
        } catch (CuacaTidakTersediaException) {
            $this->addToAssertionCount(1);
        }
    }

    private function buatPetani(?Wilayah $wilayah = null): array
    {
        $wilayah ??= Wilayah::firstOrCreate(['nama_wilayah' => 'Sukoharjo'], ['latitude' => -7.68, 'longitude' => 110.84]);
        $petani = User::create([
            'username' => 'petani-'.Str::lower(Str::random(5)), 'email' => Str::lower(Str::random(10)).'@contoh.test',
            'password' => 'rahasia123', 'role' => 'petani',
        ]);
        $profil = PetaniProfile::create(['user_id' => $petani->id, 'wilayah_id' => $wilayah->id, 'luas_lahan' => 2]);

        return [$petani, $profil];
    }

    private function buatLahan(PetaniProfile $profil, string $nama, array $titik): Lahan
    {
        return Lahan::create([
            'petani_profile_id' => $profil->id, 'nama_lahan' => $nama, 'luas_ha' => 0.5,
            'latitude' => $titik[0], 'longitude' => $titik[1], 'status' => 'aktif',
        ]);
    }

    private function palsukanOpenWeatherMap(): void
    {
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response($this->owmSekarang()),
            'api.openweathermap.org/data/2.5/forecast*' => Http::response($this->owmPrakiraan()),
        ]);
    }

    /** Contoh respons /data/2.5/weather (units=metric) */
    private function owmSekarang(array $timpa = []): array
    {
        return array_replace_recursive([
            'coord' => ['lon' => 110.84, 'lat' => -7.68],
            'weather' => [['id' => 500, 'main' => 'Rain', 'description' => 'hujan ringan', 'icon' => '10d']],
            'main' => ['temp' => 27.36, 'feels_like' => 30.4, 'pressure' => 1009, 'humidity' => 84],
            'wind' => ['speed' => 3.2, 'deg' => 230],
            'rain' => ['1h' => 0.6],
            'clouds' => ['all' => 75],
            'dt' => now()->timestamp,
            'timezone' => 25200,
            'name' => 'Sukoharjo',
            'cod' => 200,
        ], $timpa);
    }

    /** Contoh respons /data/2.5/forecast: 40 blok x 3 jam mulai 07.00 WIB */
    private function owmPrakiraan(): array
    {
        $mulai = Carbon::parse('2026-10-13 00:00:00', 'UTC')->timestamp;
        $hujan = [0, 0.5, 2.0, 1.5];
        $peluang = [0.1, 0.6, 0.85, 0.7];
        $daftar = [];

        for ($i = 0; $i < 40; $i++) {
            $blok = [
                'dt' => $mulai + $i * 10800,
                'main' => ['temp' => 26 + $i % 8, 'temp_min' => 24 + $i % 8, 'temp_max' => 27 + $i % 8, 'humidity' => 80],
                'weather' => [['id' => ($hujan[$i] ?? 0) > 0 ? 500 : 802, 'icon' => '03d']],
                'wind' => ['speed' => 2.5],
                'pop' => $peluang[$i] ?? 0,
            ];
            if (($hujan[$i] ?? 0) > 0) {
                $blok['rain'] = ['3h' => $hujan[$i]];
            }
            $daftar[] = $blok;
        }

        return ['cod' => '200', 'cnt' => 40, 'list' => $daftar, 'city' => ['name' => 'Sukoharjo', 'timezone' => 25200]];
    }

    /** Contoh respons Open-Meteo /v1/forecast */
    private function openMeteo(): array
    {
        $hujan = array_pad([0, 0, 0.2, 1.1, 1.6, 0.6], 24, 0);
        $peluang = array_pad([5, 10, 40, 65, 60, 30], 24, 0);

        return [
            'latitude' => -7.68, 'longitude' => 110.84, 'timezone' => 'Asia/Jakarta',
            'current' => [
                'time' => '2026-10-13T06:00', 'temperature_2m' => 25.8, 'relative_humidity_2m' => 91,
                'apparent_temperature' => 28.9, 'is_day' => 1, 'precipitation' => 0, 'weather_code' => 3, 'wind_speed_10m' => 4.7,
            ],
            'hourly' => ['time' => array_fill(0, 24, '2026-10-13T06:00'), 'precipitation' => $hujan, 'precipitation_probability' => $peluang],
            'daily' => [
                'time' => ['2026-10-13', '2026-10-14', '2026-10-15', '2026-10-16', '2026-10-17'],
                'weather_code' => [61, 3, 2, 80, 95],
                'temperature_2m_max' => [31.2, 31.8, 32.4, 30.9, 29.7],
                'temperature_2m_min' => [23.4, 23.1, 23.9, 23.5, 22.8],
                'precipitation_sum' => [3.5, 0, 0, 6.2, 18.4],
                'precipitation_probability_max' => [65, 15, 10, 70, 90],
            ],
        ];
    }

    /** Hasil WeatherService yang sudah dinormalkan, untuk mengisi arsip weather_data */
    private function cuacaContoh(array $timpa = []): array
    {
        return array_replace([
            'suhu' => 29.0, 'terasa_seperti' => 32.0, 'kelembaban' => 70, 'kecepatan_angin' => 8.0,
            'curah_hujan' => 0.0, 'peluang_hujan' => 10, 'kondisi_cuaca' => 'Cerah Berawan', 'ikon' => 'cloud-sun',
            'lokasi' => 'Sukoharjo', 'latitude' => -7.68, 'longitude' => 110.84, 'prakiraan' => [],
            'sumber' => WeatherService::OPENWEATHERMAP, 'diambil_at' => now()->toIso8601String(),
        ], $timpa);
    }
}
