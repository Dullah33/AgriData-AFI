<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Lahan extends Model
{
    protected $table = 'lahan';

    protected $fillable = [
        'petani_profile_id', 'nama_lahan', 'koordinat_poligon', 'luas_ha',
        'jenis_tanah', 'tanaman_aktif', 'tanggal_tanam', 'perkiraan_panen',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'koordinat_poligon' => 'array',
            'luas_ha'           => 'decimal:2',
            'tanggal_tanam'     => 'date',
            'perkiraan_panen'   => 'date',
        ];
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

    // Scope: lahan yang sedang aktif ditanami
    public function scopeAktif(Builder $query)
    {
        return $query->where('status', 'aktif');
    }
}
