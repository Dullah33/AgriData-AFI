<?php

namespace App\Exceptions;

/**
 * Dilempar saat sebuah transaksi/pembayaran diminta pindah ke status yang
 * tidak diizinkan dari status sekarang (mis. pesanan yang sudah dibatalkan
 * ditandai selesai). Pesannya aman ditampilkan ke pengguna.
 */
class TransisiStatusTidakValidException extends \RuntimeException
{
    //
}
