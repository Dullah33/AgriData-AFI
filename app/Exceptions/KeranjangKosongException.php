<?php

namespace App\Exceptions;

class KeranjangKosongException extends \RuntimeException
{
    public function __construct(string $message = 'Keranjang Anda masih kosong.')
    {
        parent::__construct($message);
    }
}
