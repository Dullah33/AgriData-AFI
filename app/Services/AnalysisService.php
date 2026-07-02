<?php

namespace App\Services;

use App\Models\Plant;

class AnalysisService
{
    /**
     * Hitung skor kecocokan (0-100) antara tanaman dan kondisi cuaca
     */
    public function calculateMatchingScore(Plant $plant, array $weather): int
    {
        $score = 0;

        // 1. Skor Suhu (Bobot 40 poin)
        $currentTemp = $weather['suhu'];
        if ($currentTemp >= $plant->min_suhu && $currentTemp <= $plant->max_suhu) {
            $score += 40;
        } else {
            // Penalti jika melenceng dari range ideal
            $distance = min(
                abs($currentTemp - $plant->min_suhu),
                abs($currentTemp - $plant->max_suhu)
            );
            $score += max(0, 40 - ($distance * 4));
        }

        // 2. Skor Kelembaban (Bobot 30 poin)
        $currentHumidity = $weather['kelembaban'];
        if ($currentHumidity >= $plant->min_kelembaban && $currentHumidity <= $plant->max_kelembaban) {
            $score += 30;
        } else {
            $distance = min(
                abs($currentHumidity - $plant->min_kelembaban),
                abs($currentHumidity - $plant->max_kelembaban)
            );
            $score += max(0, 30 - ($distance * 1.5));
        }

        // 3. Skor Curah Hujan (Bobot 30 poin)
        // Logika sederhana: cocokkan kondisi saat ini dengan preferensi tanaman
        $precipitation = $weather['curah_hujan'];
        $plantRainPref = strtolower($plant->curah_hujan_ideal ?? '');

        if ($precipitation > 50 && str_contains($plantRainPref, 'tinggi')) $score += 30;
        elseif ($precipitation > 10 && $precipitation <= 50 && str_contains($plantRainPref, 'sedang')) $score += 30;
        elseif ($precipitation <= 10 && str_contains($plantRainPref, 'rendah')) $score += 30;
        else $score += 10; // Partial score jika tidak match sempurna

        return round($score);
    }

    /**
     * Tentukan status rekomendasi berdasarkan skor
     */
    public function determineStatus(int $score): string
    {
        if ($score >= 85) return 'Sangat Cocok';
        if ($score >= 70) return 'Cocok';
        if ($score >= 50) return 'Bisa Ditanam';
        return 'Kurang Cocok';
    }

    /**
     * Generate rekomendasi teks spesifik berdasarkan kondisi
     */
    public function generateRecommendation(Plant $plant, array $weather): string
    {
        $status = $weather['kondisi_cuaca'];
        $precipitation = $weather['curah_hujan'];

        if (str_contains(strtolower($status), 'hujan') || $precipitation > 30) {
            return $plant->status_cuaca_hujan ?? 'Waspadai genangan air. Pastikan drainase lancar.';
        } elseif ($weather['suhu'] > $plant->max_suhu) {
            return $plant->status_cuaca_panas ?? 'Suhu terlalu tinggi. Pertimbangkan irigasi tambahan atau naungan.';
        } else {
            return 'Kondisi lingkungan cukup ideal. Lanjutkan pemeliharaan rutin.';
        }
    }
}