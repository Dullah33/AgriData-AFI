<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukPanen extends Model
{
    protected $table = 'produk_panen';

    protected $fillable = [
        'petani_id', 'nama_produk', 'kategori', 'deskripsi',
        'harga', 'satuan', 'stok', 'foto_produk', 'status',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'stok' => 'integer',
        ];
    }

    public function petani()
    {
        return $this->belongsTo(User::class, 'petani_id');
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'produk_id');
    }

    // Scope: hanya produk yang tersedia
    public function scopeTersedia($query)
    {
        return $query->where('status', 'tersedia')->where('stok', '>', 0);
    }

    // Bisa dimasukkan ke keranjang / dibeli
    public function bisaDibeli(): bool
    {
        return $this->status === 'tersedia' && $this->stok > 0;
    }
}
