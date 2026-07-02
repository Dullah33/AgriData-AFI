<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Artikel extends Model
{
    protected $table = 'artikel';

    protected $fillable = [
        'user_id', 'judul', 'slug', 'kategori',
        'konten', 'foto_sampul', 'status', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // Auto-generate slug dari judul saat membuat artikel baru
    protected static function booted(): void
    {
        static::creating(function ($artikel) {
            $artikel->slug = Str::slug($artikel->judul) . '-' . Str::random(5);
        });
    }

    public function penulis()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
