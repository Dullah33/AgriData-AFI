<?php

namespace App\Exceptions;

/**
 * Dilempar saat checkout kalau stok produk ternyata tidak cukup (atau
 * produk sudah tidak dijual) ketika baris produknya dikunci. Melempar
 * exception ini di dalam DB::transaction membuat seluruh checkout di-rollback.
 */
class StokTidakCukupException extends \RuntimeException
{
    public function __construct(
        public readonly string $namaProduk,
        public readonly int $stokTersedia,
        public readonly ?int $produkId = null,
    ) {
        parent::__construct($stokTersedia > 0
            ? "Stok {$namaProduk} tidak mencukupi, tersisa {$stokTersedia}. Silakan ubah jumlah di keranjang."
            : "{$namaProduk} sudah habis atau tidak dijual lagi. Silakan hapus dari keranjang.");
    }
}
