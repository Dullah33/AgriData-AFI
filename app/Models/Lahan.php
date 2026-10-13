<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lahan extends Model
{
    protected $table = 'lahan';

    protected $fillable = [
        'petani_profile_id', 'nama_lahan', 'koordinat_poligon', 'latitude', 'longitude', 'luas_ha',
        'jenis_tanah', 'tanaman_aktif', 'tanggal_tanam', 'perkiraan_panen',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'koordinat_poligon' => 'array',
            'latitude'          => 'float',
            'longitude'         => 'float',
            'luas_ha'           => 'decimal:2',
            'tanggal_tanam'     => 'date',
            'perkiraan_panen'   => 'date',
        ];
    }

    protected static function booted(): void
    {
        // Titik koordinat untuk cuaca ikut diperbarui setiap kali petani
        // menggambar ulang batas lahan di peta.
        static::saving(function (Lahan $lahan) {
            if ($lahan->isDirty('koordinat_poligon') && ($titik = $lahan->centroid())) {
                $lahan->latitude = round($titik[0], 7);
                $lahan->longitude = round($titik[1], 7);
            }
        });
    }

    public function petaniProfile()
    {
        return $this->belongsTo(PetaniProfile::class, 'petani_profile_id');
    }

    // Riwayat laporan deteksi penyakit AI Scanner yang terkait lahan ini
    public function diseaseReports()
    {
        return $this->hasMany(DiseaseReport::class, 'lahan_id');
    }

    // Titik tengah (centroid) poligon lahan — dipakai sebagai lokasi
    // default laporan penyakit yang dibuat untuk lahan ini, supaya
    // laporan tersebut bisa muncul di peta tanpa perlu GPS asli.
    public function centroid(): ?array
    {
        if (! is_array($this->koordinat_poligon) || count($this->koordinat_poligon) < 3) {
            return null;
        }

        $lat = collect($this->koordinat_poligon)->avg(fn ($p) => $p[0]);
        $lng = collect($this->koordinat_poligon)->avg(fn ($p) => $p[1]);

        return [$lat, $lng];
    }

    // Cuaca terakhir yang disimpan job pembaruan harian (cuaca:perbarui)
    public function weatherData(): HasOne
    {
        return $this->hasOne(WeatherData::class, 'lahan_id');
    }

    // Koordinat [lat, lng] untuk mengambil cuaca. Kalau batas lahan belum
    // digambar, pakai koordinat wilayah (kecamatan) tempat petani terdaftar.
    public function koordinatCuaca(): ?array
    {
        if ($this->latitude !== null && $this->longitude !== null) {
            return [(float) $this->latitude, (float) $this->longitude];
        }

        $wilayah = $this->petaniProfile?->wilayah;

        if ($wilayah?->latitude !== null && $wilayah?->longitude !== null) {
            return [(float) $wilayah->latitude, (float) $wilayah->longitude];
        }

        return null;
    }

    // Scope: lahan yang sedang aktif ditanami
    public function scopeAktif(Builder $query)
    {
        return $query->where('status', 'aktif');
    }
}
