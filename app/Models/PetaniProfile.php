<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetaniProfile extends Model
{
    protected $table = 'petani_profile';

    protected $fillable = [
        'user_id', 'wilayah_id', 'nama_kelompok_tani', 'luas_lahan',
        'status_aktif', 'nik', 'alamat', 'komoditas_utama', 'foto_ktp',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
            'luas_lahan'   => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    // Admin yang memverifikasi profil petani ini
    public function verifikator()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Daftar lahan milik petani ini
    public function lahans()
    {
        return $this->hasMany(Lahan::class, 'petani_profile_id');
    }

    // Status verifikasi ditentukan dari ada/tidaknya verified_by,
    // bukan kolom enum terpisah — konsisten dengan desain migration
    // 2026_07_01_000001_add_columns_to_petani_profile_table.
    public function isTerverifikasi(): bool
    {
        return ! is_null($this->verified_by);
    }

    // Scope: profil yang belum diverifikasi admin
    public function scopeBelumTerverifikasi($query)
    {
        return $query->whereNull('verified_by');
    }

    // Scope: profil yang sudah diverifikasi admin
    public function scopeTerverifikasi($query)
    {
        return $query->whereNotNull('verified_by');
    }
}