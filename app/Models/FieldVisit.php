<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldVisit extends Model
{
    protected $table = 'field_visits';

    protected $fillable = [
        'extension_officer_id', 'petani_profile_id', 'tanggal_kunjungan',
        'catatan_persiapan', 'status', 'kondisi_lahan', 'kendala_ditemukan',
        'rekomendasi', 'foto_dokumentasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kunjungan' => 'date',
        ];
    }

    public function extensionOfficer()
    {
        return $this->belongsTo(ExtensionOfficer::class, 'extension_officer_id');
    }

    public function petaniProfile()
    {
        return $this->belongsTo(PetaniProfile::class, 'petani_profile_id');
    }

    public function scopeTerjadwal($query)
    {
        return $query->where('status', 'terjadwal');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal_kunjungan', now()->month)
            ->whereYear('tanggal_kunjungan', now()->year);
    }
}
