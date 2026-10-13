<?php

namespace App\Console\Commands;

use App\Exceptions\CuacaTidakTersediaException;
use App\Jobs\PerbaruiCuacaLokasi;
use App\Models\Lahan;
use App\Services\WeatherService;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PerbaruiCuacaLahan extends Command
{
    protected $signature = 'cuaca:perbarui
                            {--lahan=* : Hanya perbarui lahan dengan ID ini}
                            {--sync : Jalankan langsung tanpa queue (untuk uji coba, tanpa rate limit)}';

    protected $description = 'Ambil cuaca terbaru semua lahan lewat queue dan simpan ke tabel weather_data';

    public function handle(WeatherService $cuaca): int
    {
        $lokasi = [];
        $dilewati = 0;

        $query = Lahan::with('petaniProfile.wilayah')
            ->when($this->option('lahan'), fn ($q, $ids) => $q->whereKey($ids));

        // Kelompokkan lahan per titik koordinat yang sudah dibulatkan, supaya
        // lahan yang berdekatan cukup satu kali memanggil API.
        foreach ($query->lazyById(200) as $lahan) {
            try {
                $titik = $lahan->koordinatCuaca();

                if (! $titik) {
                    $dilewati++;

                    continue;
                }

                [$lat, $lon] = $cuaca->bulatkan(...$titik);
            } catch (InvalidArgumentException) {
                $dilewati++;

                continue;
            }

            $kunci = $cuaca->kunciCache($lat, $lon);
            $lokasi[$kunci] ??= ['lat' => $lat, 'lon' => $lon, 'lahan' => []];
            $lokasi[$kunci]['lahan'][] = $lahan->id;
        }

        if ($dilewati) {
            $this->warn("{$dilewati} lahan dilewati karena belum punya koordinat (batas lahan belum digambar di peta).");
        }

        if (! $lokasi) {
            $this->info('Tidak ada lahan yang perlu diperbarui.');

            return self::SUCCESS;
        }

        $jobs = collect($lokasi)->map(fn ($l) => new PerbaruiCuacaLokasi($l['lat'], $l['lon'], $l['lahan']))->values();
        $jumlahLahan = collect($lokasi)->sum(fn ($l) => count($l['lahan']));

        if ($this->option('sync')) {
            return $this->jalankanLangsung($jobs->all(), $cuaca, $jumlahLahan);
        }

        $batch = Bus::batch($jobs->all())
            ->name('Pembaruan cuaca lahan '.now(config('cuaca.zona_waktu'))->format('d-m-Y H:i'))
            ->onQueue(config('cuaca.antrian'))
            ->allowFailures()
            ->finally(static function (Batch $batch) {
                Log::info('Cuaca: pembaruan harian selesai', [
                    'batch' => $batch->id,
                    'lokasi' => $batch->totalJobs,
                    'gagal' => $batch->failedJobs,
                ]);
            })
            ->dispatch();

        $this->info("{$jobs->count()} lokasi ({$jumlahLahan} lahan) masuk antrean. Batch: {$batch->id}");

        return self::SUCCESS;
    }

    /**
     * Tanpa queue: langsung memanggil API untuk setiap lokasi.
     */
    private function jalankanLangsung(array $jobs, WeatherService $cuaca, int $jumlahLahan): int
    {
        $berhasil = 0;

        foreach ($jobs as $job) {
            try {
                $job->handle($cuaca);
                $berhasil++;
            } catch (CuacaTidakTersediaException $e) {
                $this->error("Gagal di {$job->latitude},{$job->longitude}: {$e->getMessage()}");
            }
        }

        $this->info("{$berhasil} dari ".count($jobs)." lokasi diperbarui ({$jumlahLahan} lahan).");

        return $berhasil === count($jobs) ? self::SUCCESS : self::FAILURE;
    }
}
