<?php

namespace App\Console\Commands;

use App\Services\Marketplace\PembayaranService;
use Illuminate\Console\Command;

class KedaluwarsakanPembayaran extends Command
{
    protected $signature = 'pembayaran:kedaluwarsa';

    protected $description = 'Batalkan pesanan yang belum dibayar sampai batas waktu dan kembalikan stoknya';

    public function handle(PembayaranService $pembayaran): int
    {
        $jumlah = $pembayaran->kedaluwarsakanJatuhTempo();

        $this->info("{$jumlah} pembayaran ditandai kedaluwarsa.");

        return self::SUCCESS;
    }
}
