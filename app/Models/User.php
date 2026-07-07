<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $fillable = [
        'username', 'email', 'password', 'role',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // Helper role checks
    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isPetani(): bool   { return $this->role === 'petani'; }
    public function isPenyuluh(): bool { return $this->role === 'penyuluh'; }
    public function isUser(): bool     { return $this->role === 'user'; }

    public function artikels()
    {
        return $this->hasMany(Artikel::class, 'user_id');
    }

    // Profil lahan/petani milik user ini (hanya relevan untuk role petani)
    public function petaniProfile()
    {
        return $this->hasOne(PetaniProfile::class, 'user_id');
    }

    // Profil penyuluh milik user ini (hanya relevan untuk role penyuluh)
    public function extensionOfficer()
    {
        return $this->hasOne(ExtensionOfficer::class, 'user_id');
    }

    // Laporan deteksi penyakit yang pernah dibuat user ini lewat AI Scanner
    public function diseaseReports()
    {
        return $this->hasMany(DiseaseReport::class, 'user_id');
    }
}
