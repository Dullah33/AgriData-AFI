<?php

namespace App\Services;

use App\Exceptions\PlantNotDetectedException;
use Illuminate\Support\Facades\Http;

/**
 * Integrasi dengan Plant.id API v3 milik Kindwise (https://www.kindwise.com/plant-id).
 *
 * Dipakai sebagai pengganti mesin klasifikasi katalog lokal di
 * DiseaseDetectionService begitu AI_SCANNER_PROVIDER=plantid diaktifkan
 * di .env. Endpoint yang dipakai: identification dengan parameter
 * `health=only` (BAB 5.3.1 dokumen modul sistem menyebut deteksi
 * penyakit lewat API AI eksternal — Plant.id berperan sebagai
 * implementasi API tersebut).
 *
 * Dokumentasi resmi: https://documenter.getpostman.com/view/24599534/2s93z5A4v2
 */
class PlantIdService
{
    private const ENDPOINT = 'https://api.plant.id/v3/identification';

    // Detail penyakit yang diminta ke Plant.id (BAB 2.5.1: gejala, penyebab, penanganan)
    private const DISEASE_DETAILS = 'local_name,description,treatment,cause,common_names';

    public function analisis(string $absoluteImagePath): array
    {
        $apiKey = config('services.plant_id.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException('PLANT_ID_API_KEY belum diatur di .env.');
        }

        $base64 = base64_encode(file_get_contents($absoluteImagePath));

        $response = Http::timeout(20)
            ->withHeaders([
                'Api-Key'      => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->withQueryParameters([
                'details'  => self::DISEASE_DETAILS,
                'language' => 'id,en', // Indonesia diutamakan, fallback ke Inggris kalau Plant.id belum punya terjemahan Indonesianya
            ])
            ->post(self::ENDPOINT, [
                'images' => [$base64],
                // 'only' = hanya health assessment yang dijalankan (1 kredit, bukan 2)
                'health' => 'only',
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Plant.id API merespons dengan error: '.$response->status().' — '.$response->body());
        }

        $json = $response->json();

        $isPlant = $json['result']['is_plant']['binary'] ?? null;
        if ($isPlant === false) {
            throw new PlantNotDetectedException('Foto yang diupload sepertinya bukan tanaman. Silakan upload ulang dengan foto yang lebih jelas.');
        }

        $isHealthy = $json['result']['is_healthy']['binary'] ?? null;
        $suggestions = $json['result']['disease']['suggestions'] ?? [];

        // Kasus tanaman terdeteksi sehat / tidak ada saran penyakit
        if ($isHealthy === true || empty($suggestions)) {
            return [
                'nama_penyakit'    => 'Tanaman Terdeteksi Sehat',
                'confidence_score' => round((float) ($json['result']['is_healthy']['probability'] ?? 0.9) * 100, 2),
                'gejala'           => 'Tidak ditemukan indikasi penyakit, hama, atau kekurangan nutrisi pada foto yang diupload.',
                'penyebab'         => '-',
                'penanganan'       => 'Lanjutkan perawatan rutin (penyiraman, pemupukan, dan pemantauan berkala) untuk menjaga kondisi tanaman tetap sehat.',
                'tingkat_risiko'   => 'rendah',
            ];
        }

        // Ambil saran dengan probabilitas tertinggi (suggestions sudah terurut dari API)
        $top = $suggestions[0];
        $details = $top['details'] ?? [];
        $treatment = $details['treatment'] ?? [];

        $penanganan = collect([
            $treatment['biological'] ?? null,
            $treatment['chemical'] ?? null,
        ])->filter()->implode(' ');

        if (empty($penanganan)) {
            $penanganan = $treatment['prevention'] ?? 'Tidak ada rekomendasi penanganan spesifik dari Plant.id untuk kelas ini.';
        }

        $probability = (float) ($top['probability'] ?? 0);

        return [
            'nama_penyakit'    => $details['local_name'] ?? $top['name'] ?? 'Tidak Teridentifikasi',
            'confidence_score' => round($probability * 100, 2),
            'gejala'           => $details['description'] ?? 'Deskripsi tidak tersedia dari Plant.id untuk kelas ini.',
            'penyebab'         => $details['cause'] ?? '-',
            'penanganan'       => $penanganan,
            'tingkat_risiko'   => $this->tingkatRisikoDariProbabilitas($probability),
        ];
    }

    private function tingkatRisikoDariProbabilitas(float $probability): string
    {
        return match (true) {
            $probability >= 0.7 => 'tinggi',
            $probability >= 0.4 => 'sedang',
            default             => 'rendah',
        };
    }
}
