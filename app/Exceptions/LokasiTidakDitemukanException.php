<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Nama wilayah tidak bisa diubah menjadi koordinat (geocoding gagal dan
 * tidak ada di data cadangan). Pesannya aman ditampilkan ke pengguna.
 */
class LokasiTidakDitemukanException extends RuntimeException
{
}
