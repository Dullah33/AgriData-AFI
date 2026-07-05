<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    use HasFactory;
    protected $fillable = [
        'kode',
        'nama',
        'gambar',
        'suhu_ideal',
        'min_suhu',
        'max_suhu',
        'kelembapan_ideal',
        'min_kelembaban',
        'max_kelembaban',
        'curah_hujan_ideal',
        'musim',
        'jenis_tanah',
        'durasi_panen',
        'status_cuaca_hujan',
        'status_cuaca_panas',
        'status_curah_hujan_tinggi',
        'status_curah_hujan_rendah',
        'deskripsi',
        'keunggulan',
        'budidaya_persiapan_lahan',
        'budidaya_pemupukan',
        'budidaya_irigasi',
        'langkah_budidaya',
        'tips_budidaya',

    ];

    protected function casts(): array
    {
        return [
            'min_suhu' => 'decimal:2',
            'max_suhu' => 'decimal:2',
            'min_kelembapan' => 'decimal:2',
            'max_kelembapan' => 'decimal:2',
            'langkah_budidaya' => 'array',
            'tips_budidaya' => 'array',
        ];
    }

    public function isTemperatureSuitable($currentTemp)
    {
        return $currentTemp >= $this->min_suhu && $currentTemp <= $this->max_suhu;
    }

    public function isHumiditySuitable($currentHumidity)
    {
        return $currentHumidity >= $this->min_kelembapan && $currentHumidity <= $this->max_kelembapan;
    }

    public function getMatchingScore($weatherData)
    {
        $score = 0;

        if ($this->isTemperatureSuitable($weatherData['suhu'])) {
            $score += 40;
        } else {
            $distance = min(
                abs($weatherData['suhu'] - $this->min_suhu),
                abs($weatherData['suhu'] - $this->max_suhu)
            );
            $score += max(0, 40 - ($distance * 5));
        }

        if ($this->isHumiditySuitable($weatherData['kelembapan'])) {
            $score += 30;
        } else {
            $distance = min(
                abs($weatherData['kelembapan'] - $this->min_kelembapan),
                abs($weatherData['kelembapan'] - $this->max_kelembapan)
            );
            $score += max(0, 30 - ($distance * 2));
        }

        return round($score);
    }
}
