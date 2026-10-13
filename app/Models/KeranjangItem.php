<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu produk di keranjang pembeli. Keranjang tidak mengunci stok, jadi
 * jumlahnya bisa saja melebihi stok kalau produk keburu dibeli orang lain.
 * masalahStok() dipakai untuk memberi tahu pembeli sebelum checkout.
 */
class KeranjangItem extends Model
{
    protected $table = 'keranjang';

    protected $fillable = ['user_id', 'produk_id', 'jumlah'];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    public function produk()
    {
        return $this->belongsTo(ProdukPanen::class, 'produk_id');
    }

    public function pemilik()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subtotal(): float
    {
        return (float) ($this->produk?->harga ?? 0) * $this->jumlah;
    }

    // Pesan masalah stok, atau null kalau item siap di-checkout
    public function masalahStok(): ?string
    {
        $produk = $this->produk;

        if (! $produk || $produk->status !== 'tersedia') {
            return 'Produk ini sudah tidak dijual.';
        }

        if ($produk->stok < 1) {
            return 'Stok habis.';
        }

        if ($this->jumlah > $produk->stok) {
            return "Stok tinggal {$produk->stok} {$produk->satuan}.";
        }

        return null;
    }
}
