<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ExtensionOfficer extends Model
{
    protected $table = 'extension_officers';

    protected $fillable = [
        'user_id', 'nip', 'wilayah_binaan', 'phone', 'status', 'assigned_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Admin yang menugaskan/membuat akun penyuluh ini
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function fieldVisits()
    {
        return $this->hasMany(FieldVisit::class, 'extension_officer_id');
    }

    public function trainingPrograms()
    {
        return $this->hasMany(TrainingProgram::class, 'extension_officer_id');
    }

    public function monthlyReports()
    {
        return $this->hasMany(MonthlyReport::class, 'extension_officer_id');
    }

    // Petani binaan: dicocokkan lewat nama wilayah (desa/kecamatan) yang
    // sama dengan wilayah_binaan milik penyuluh. Dipakai sebagai query supaya
    // bisa langsung di-count/whereKey tanpa memuat semua baris ke memori.
    public function petaniBinaanQuery(): Builder
    {
        return PetaniProfile::whereHas('wilayah', fn ($q) => $q->where('nama_wilayah', $this->wilayah_binaan));
    }

    // Daftar petani binaan untuk ditampilkan. Relasi user ikut di-eager load
    // karena view selalu menampilkan username (sebelumnya N+1: satu query
    // users per petani).
    public function petaniBinaan(): Collection
    {
        return $this->petaniBinaanQuery()->with(['user', 'wilayah'])->get();
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
