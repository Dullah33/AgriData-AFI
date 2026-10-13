<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class WeatherData extends Model
{
    use HasFactory;

    /**
     * Fillable attributes
     */
    protected $fillable = [
        'lahan_id',
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
        'sumber',
        'diambil_at',
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
            'diambil_at' => 'datetime',
        ];
    }

    // Jumlah hari ringkasan cuaca harian yang disimpan di historical_data
    public const HARI_RIWAYAT = 30;

    public function lahan(): BelongsTo
    {
        return $this->belongsTo(Lahan::class, 'lahan_id');
    }

    /**
     * Simpan hasil WeatherService untuk satu lahan (satu baris per lahan).
     * Ringkasan per hari ikut dicatat di historical_data, maksimal 30 hari
     * terakhir, supaya tren cuaca di lahan bisa dilihat tanpa memanggil API.
     */
    public static function simpanUntukLahan(Lahan $lahan, array $cuaca): self
    {
        $diambil = Carbon::parse($cuaca['diambil_at']);
        $tanggal = $diambil->copy()->setTimezone(config('cuaca.zona_waktu'))->toDateString();
        $lama = static::firstWhere('lahan_id', $lahan->id);

        $riwayat = collect($lama?->historical_data ?? [])
            ->reject(fn ($hari) => ($hari['tanggal'] ?? null) === $tanggal)
            ->push([
                'tanggal' => $tanggal,
                'suhu' => $cuaca['suhu'],
                'kelembaban' => $cuaca['kelembaban'],
                'curah_hujan' => $cuaca['curah_hujan'],
                'kondisi_cuaca' => $cuaca['kondisi_cuaca'],
            ])
            ->sortBy('tanggal')
            ->take(-self::HARI_RIWAYAT)
            ->values()
            ->all();

        return static::updateOrCreate(['lahan_id' => $lahan->id], [
            'nama_lokasi' => $lahan->nama_lahan,
            'kabupaten' => $lahan->petaniProfile?->wilayah?->nama_wilayah,
            'latitude' => $cuaca['latitude'],
            'longitude' => $cuaca['longitude'],
            'suhu' => $cuaca['suhu'],
            'kelembaban' => $cuaca['kelembaban'],
            'curah_hujan' => $cuaca['curah_hujan'],
            'kecepatan_angin' => $cuaca['kecepatan_angin'],
            'kondisi_cuaca' => $cuaca['kondisi_cuaca'],
            'sumber' => $cuaca['sumber'],
            'diambil_at' => $diambil,
            'forecast_data' => $cuaca['prakiraan'],
            'weather_data' => $cuaca,
            'historical_data' => $riwayat,
        ]);
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