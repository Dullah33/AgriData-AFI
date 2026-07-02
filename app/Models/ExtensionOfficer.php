<?php

namespace App\Models;

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

    // Daftar petani binaan: dicocokkan lewat nama wilayah (desa/kecamatan)
    // yang sama dengan wilayah_binaan milik penyuluh, mengacu ke relasi
    // petani -> wilayah pada tabel wilayah/petani_profile.
    public function petaniBinaan()
    {
        return PetaniProfile::whereHas('wilayah', function ($q) {
            $q->where('nama_wilayah', $this->wilayah_binaan);
        })->with('wilayah')->get();
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
