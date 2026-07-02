<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UlasanRating extends Model
{
    protected $table = 'ulasan_rating';

    protected $fillable = [
        'transaksi_id', 'rating', 'komentar', 'status',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }
}
