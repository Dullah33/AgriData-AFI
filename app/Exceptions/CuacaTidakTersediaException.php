<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Semua provider cuaca gagal dihubungi (timeout, kuota habis, key salah,
 * atau respons rusak). Detail teknisnya sudah dicatat di log.
 */
class CuacaTidakTersediaException extends RuntimeException
{
    public function __construct(string $message = 'Data cuaca belum bisa diambil. Coba lagi beberapa saat lagi.', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
