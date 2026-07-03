<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Artikel extends Model
{
    protected $table = 'artikel';

    // Kolom-kolom di bawah ini disamakan dengan migration ASLI
    // (2026_06_14_200509_create_artikel_table.php +
    // 2026_07_02_000004_add_columns_to_artikel_table.php), BUKAN nama
    // kolom bahasa Inggris versi dokumen BAB 4.2.9. Sebelumnya model ini
    // memakai title/category/content/thumbnail/author_id yang tidak ada
    // di database sama sekali -> Artikel::create() dari ArtikelController
    // selalu gagal mengisi judul/kategori/konten/foto_sampul karena
    // mass-assignment silently menolak kolom yang tidak terdaftar di sini.
    protected $fillable = [
        'user_id',
        'judul',
        'slug',
        'konten',
        'foto_sampul',
        'kategori',
        'status',
        'published_at',
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

    // Scope: hanya artikel yang sudah dipublish (untuk halaman baca publik)
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
