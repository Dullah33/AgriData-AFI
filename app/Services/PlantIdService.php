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

    public function __construct(private ?GeminiEnricherService $enricher = null)
    {
        $this->enricher ??= new GeminiEnricherService();
    }

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
            $this->teksLokal($treatment['biological'] ?? null),
            $this->teksLokal($treatment['chemical'] ?? null),
        ])->filter()->implode(' ');

        if (empty($penanganan)) {
            $penanganan = $this->teksLokal($treatment['prevention'] ?? null)
                ?? 'Tidak ada rekomendasi penanganan spesifik dari Plant.id untuk kelas ini.';
        }

        $probability = (float) ($top['probability'] ?? 0);

        $namaPenyakit = $this->teksLokal($details['local_name'] ?? null) ?? $this->teksLokal($top['name'] ?? null) ?? 'Tidak Teridentifikasi';
        $gejala = $this->teksLokal($details['description'] ?? null) ?? 'Deskripsi tidak tersedia dari Plant.id untuk kelas ini.';
        $penyebab = $this->teksLokal($details['cause'] ?? null) ?? '-';

        // Kalau Plant.id tidak menyediakan penyebab dan/atau penanganan
        // spesifik untuk kelas ini, lengkapi lewat Gemini API (kalau
        // dikonfigurasi). Kalau enrichment gagal/tidak dikonfigurasi,
        // teks fallback bawaan di atas tetap dipakai — tidak ada error.
        $butuhPenyebab = $penyebab === '-';
        $butuhPenanganan = empty($penanganan) || $penanganan === 'Tidak ada rekomendasi penanganan spesifik dari Plant.id untuk kelas ini.';

        if ($butuhPenyebab || $butuhPenanganan) {
            $hasilEnrich = $this->enricher->lengkapi($namaPenyakit, $gejala);

            if ($hasilEnrich !== null) {
                if ($butuhPenyebab && ! empty($hasilEnrich['penyebab'])) {
                    $penyebab = $hasilEnrich['penyebab'];
                }
                if ($butuhPenanganan && ! empty($hasilEnrich['penanganan'])) {
                    $penanganan = $hasilEnrich['penanganan'];
                }
            }
        }

        return [
            'nama_penyakit'    => $namaPenyakit,
            'confidence_score' => round($probability * 100, 2),
            'gejala'           => $gejala,
            'penyebab'         => $penyebab,
            'penanganan'       => $penanganan,
            'tingkat_risiko'   => $this->tingkatRisikoDariProbabilitas($probability),
        ];
    }

    /**
     * Karena parameter `language` yang berisi lebih dari satu bahasa
     * (mis. "id,en") membuat Plant.id mengembalikan field terlokalisasi
     * (local_name, description, cause, treatment.*) sebagai object
     * per-bahasa (mis. {"id": null, "en": "..."}) alih-alih string biasa,
     * method ini menangani KEDUA kemungkinan bentuk dengan aman:
     *   - string           -> dipakai langsung
     *   - array/object     -> ambil bahasa Indonesia dulu, kalau kosong
     *                         fallback ke Inggris, kalau masih kosong
     *                         ambil nilai pertama yang tidak kosong
     *   - null/kosong      -> null (biar pemanggil bisa kasih fallback teks)
     *
     * Tanpa ini, array akan lolos ke Eloquent dan meledak jadi error
     * "Array to string conversion" saat disimpan ke database.
     */
    private function teksLokal(mixed $value, array $prioritasBahasa = ['id', 'en']): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($prioritasBahasa as $lang) {
                if (! empty($value[$lang]) && is_string($value[$lang])) {
                    return $value[$lang];
                }
            }

            foreach ($value as $isi) {
                if (is_string($isi) && $isi !== '') {
                    return $isi;
                }
            }

            return null;
        }

        return (string) $value;
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