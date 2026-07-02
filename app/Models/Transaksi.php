<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'user_id', 'produk_id', 'jumlah', 'total_harga',
        'status_transaksi', 'alamat_pengiriman', 'catatan',
    ];

    public function pembeli()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function produk()
    {
        return $this->belongsTo(ProdukPanen::class, 'produk_id');
    }

    public function ulasan()
    {
        return $this->hasOne(UlasanRating::class, 'transaksi_id');
    }
}
