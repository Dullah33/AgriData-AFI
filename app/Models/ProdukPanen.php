<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukPanen extends Model
{
    protected $table = 'produk_panen';

    public const KATEGORI = [
        'Sayuran', 'Buah-buahan', 'Biji-bijian',
        'Umbi-umbian', 'Rempah-rempah', 'Lainnya',
    ];

    // Status yang masih terlihat. 'dihapus' adalah hapus lunak: produk
    // disembunyikan tapi barisnya tetap ada supaya riwayat transaksi utuh.
    public const STATUS_TERLIHAT = ['tersedia', 'habis'];

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

    // Scope: produk yang belum dihapus (tersedia maupun habis)
    public function scopeTerlihat($query)
    {
        return $query->whereIn('status', self::STATUS_TERLIHAT);
    }

    // Bisa dimasukkan ke keranjang / dibeli
    public function bisaDibeli(): bool
    {
        return $this->status === 'tersedia' && $this->stok > 0;
    }
}
