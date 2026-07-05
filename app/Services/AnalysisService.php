<?php

namespace App\Services;

use App\Models\Plant;

class AnalysisService
{
    /**
     * Hitung skor kecocokan (0-100) antara tanaman dan kondisi cuaca
     */
    public function calculateMatchingScore(Plant $plant, array $weather): array
    {
        $score = 0;
        $details = [
            'suhu' => [
                'current' => $weather['suhu'],
                'ideal' => $plant->suhu_ideal,
                'match' => false
            ],
            'kelembaban' => [
                'current' => $weather['kelembaban'],
                'ideal' => $plant->kelembapan_ideal,
                'match' => false
            ]
        ];

        // 1. Skor Suhu (40 poin)
        $currentTemp = (float) $weather['suhu'];
        $minSuhu = (float) $plant->min_suhu;
        $maxSuhu = (float) $plant->max_suhu;
        
        if ($currentTemp >= $minSuhu && $currentTemp <= $maxSuhu) {
            $score += 40;
            $details['suhu']['match'] = true;
        } else {
            // Partial score jika mendekati
            $distance = min(
                abs($currentTemp - $minSuhu),
                abs($currentTemp - $maxSuhu)
            );
            $partialScore = max(0, 40 - ($distance * 4));
            $score += $partialScore;
        }

        // 2. Skor Kelembaban (30 poin)
        $currentHumidity = (float) $weather['kelembaban'];
        $minHumidity = (float) $plant->min_kelembaban;
        $maxHumidity = (float) $plant->max_kelembaban;
        
        if ($currentHumidity >= $minHumidity && $currentHumidity <= $maxHumidity) {
            $score += 30;
            $details['kelembaban']['match'] = true;
        } else {
            $distance = min(
                abs($currentHumidity - $minHumidity),
                abs($currentHumidity - $maxHumidity)
            );
            $partialScore = max(0, 30 - ($distance * 1.5));
            $score += $partialScore;
        }

        // 3. Skor Curah Hujan (30 poin)
        $precipitation = (float) ($weather['curah_hujan'] ?? 0);
        $plantRainPref = strtolower($plant->curah_hujan_ideal ?? '');
        
        if ($precipitation > 50 && str_contains($plantRainPref, 'tinggi')) {
            $score += 30;
        } elseif ($precipitation > 10 && $precipitation <= 50 && str_contains($plantRainPref, 'sedang')) {
            $score += 30;
        } elseif ($precipitation <= 10 && str_contains($plantRainPref, 'rendah')) {
            $score += 30;
        } else {
            $score += 10; // Partial score
        }

        return [
            'score' => round($score),
            'details' => $details
        ];
    }

    /**
     * Tentukan status rekomendasi berdasarkan skor
     */
    public function determineStatus(int $score, array $matchDetails = []): string
    {
        $suhuMatch = $matchDetails['suhu']['match'] ?? false;
        $kelembabanMatch = $matchDetails['kelembaban']['match'] ?? false;
        
        // Jika salah satu parameter kritis tidak matc
        if (!$suhuMatch || !$kelembabanMatch) {
            if ($score >= 85) return 'Cocok'; 
            if ($score >= 70) return 'Bisa Ditanam';
            if ($score >= 50) return 'Kurang Cocok';
            return 'Tidak Cocok';
        }
        
        // Jika semua match, gunakan threshold normal
        if ($score >= 85) return 'Sangat Cocok';
        if ($score >= 70) return 'Cocok';
        if ($score >= 50) return 'Bisa Ditanam';
        return 'Kurang Cocok';
    }

    /**
     * Generate rekomendasi teks spesifik
     */
    public function generateRecommendation(Plant $plant, array $weather): string
    {
        $status = $weather['kondisi_cuaca'] ?? '';
        $precipitation = $weather['curah_hujan'] ?? 0;

        if (str_contains(strtolower($status), 'hujan') || $precipitation > 30) {
            return $plant->status_cuaca_hujan ?? 'Waspadai genangan air.';
        } elseif ($weather['suhu'] > ($plant->max_suhu ?? 30)) {
            return $plant->status_cuaca_panas ?? 'Suhu terlalu tinggi.';
        } else {
            return 'Kondisi lingkungan cukup ideal. Lanjutkan pemeliharaan rutin.';
        }
    }
}