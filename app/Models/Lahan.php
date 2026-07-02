<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    // Scope: lahan yang sedang aktif ditanami
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
