<?php

namespace App\Services;

use App\Exceptions\CuacaTidakTersediaException;
use App\Exceptions\LokasiTidakDitemukanException;
use App\Models\Plant;
use Illuminate\Support\Collection;

/**
 * Alur halaman "Analisis Cuaca & Jadwal Tanam": nama wilayah -> koordinat
 * -> cuaca real-time -> skor kecocokan tiap tanaman. Sebelumnya seluruh
 * alur ini (dan penanganan error-nya) ditulis dua kali di WeatherController.
 */
class RekomendasiTanamService
{
    public function __construct(
        private LocationService $lokasi,
        private WeatherService $cuaca,
        private AnalysisService $analisis,
    ) {}

    /**
     * @return array{lokasi: array, cuaca: array}
     *
     * @throws LokasiTidakDitemukanException
     * @throws CuacaTidakTersediaException
     */
    public function kondisi(string $provinsi, string $kabupaten, string $kecamatan): array
    {
        $koordinat = $this->lokasi->geocode($provinsi, $kabupaten, $kecamatan);

        if (! isset($koordinat['latitude'], $koordinat['longitude'])) {
            throw new LokasiTidakDitemukanException('Gagal mendapatkan koordinat lokasi. Pastikan nama wilayah benar.');
        }

        return [
            'lokasi' => [
                'provinsi'  => $provinsi,
                'kabupaten' => $kabupaten,
                'kecamatan' => $kecamatan,
                'latitude'  => $koordinat['latitude'],
                'longitude' => $koordinat['longitude'],
            ],
            'cuaca' => $this->cuaca->getWeather((float) $koordinat['latitude'], (float) $koordinat['longitude']),
        ];
    }

    /**
     * Lima tanaman dengan skor kecocokan tertinggi untuk cuaca tersebut.
     */
    public function teratas(array $cuaca, int $jumlah = 5): Collection
    {
        return Plant::all()
            ->map(function (Plant $plant) use ($cuaca) {
                $hasil = $this->analisis->calculateMatchingScore($plant, $cuaca);

                return [
                    'id'     => $plant->id,
                    'kode'   => $plant->kode,
                    'nama'   => $plant->nama,
                    'gambar' => $plant->gambar ? asset($plant->gambar) : null,
                    'score'  => $hasil['score'],
                    'status' => $this->analisis->determineStatus($hasil['score'], $hasil['details']),
                    'current_conditions' => [
                        'suhu'        => $cuaca['suhu'],
                        'kelembaban'  => $cuaca['kelembaban'],
                        'curah_hujan' => $cuaca['curah_hujan'],
                    ],
                    'match_details' => [
                        'suhu_match'       => $hasil['details']['suhu']['match'],
                        'kelembaban_match' => $hasil['details']['kelembaban']['match'],
                        'musim_match'      => stripos($plant->musim ?? '', 'hujan') !== false,
                    ],
                ];
            })
            ->sortByDesc('score')
            ->take($jumlah)
            ->values();
    }

    /**
     * Analisis detail satu tanaman terhadap cuaca saat ini.
     */
    public function analisis(Plant $plant, array $cuaca): array
    {
        $hasil = $this->analisis->calculateMatchingScore($plant, $cuaca);

        return [
            'score'          => $hasil['score'],
            'status'         => $this->analisis->determineStatus($hasil['score'], $hasil['details']),
            'recommendation' => $this->analisis->generateRecommendation($plant, $cuaca),
            'match_details'  => $hasil['details'],
        ];
    }
}
