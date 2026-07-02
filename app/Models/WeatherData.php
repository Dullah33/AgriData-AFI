<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherData extends Model
{
    use HasFactory;

    /**
     * Fillable attributes
     */
    protected $fillable = [
        'provinsi',
        'kabupaten',
        'kecamatan',
        'nama_lokasi',
        'latitude',
        'longitude',
        'suhu',
        'kelembaban',
        'curah_hujan',
        'kecepatan_angin',
        'kondisi_cuaca',
        'historical_data',
        'forecast_data',
        'weather_data',
    ];

    /**
     * Cast attributes to native types
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'suhu' => 'decimal:2',
            'kelembaban' => 'decimal:2',
            'curah_hujan' => 'decimal:2',
            'kecepatan_angin' => 'decimal:2',
            'historical_data' => 'array',
            'forecast_data' => 'array',
            'weather_data' => 'array',
        ];
    }

    /**
     * Scope to find by location
     */
    public function scopeByLocation($query, $provinsi, $kabupaten, $kecamatan)
    {
        return $query->where('provinsi', $provinsi)
                    ->where('kabupaten', $kabupaten)
                    ->where('kecamatan', $kecamatan);
    }

    /**
     * Scope to find by coordinates
     */
    public function scopeByCoordinates($query, $lat, $lon, $radius = 0.01)
    {
        return $query->whereBetween('latitude', [$lat - $radius, $lat + $radius])
                    ->whereBetween('longitude', [$lon - $radius, $lon + $radius]);
    }

    /**
     * Check if data is fresh (updated within last 2 hours)
     */
    public function isFresh()
    {
        return $this->updated_at->diffInMinutes(now()) < 120;
    }
}