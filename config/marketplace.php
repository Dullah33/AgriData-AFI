<?php

/*
| Pengaturan Marketplace Hasil Panen (keranjang, checkout, pembayaran).
*/

return [

    // Berapa jam pembeli diberi waktu membayar sebelum pesanan dibatalkan
    // otomatis dan stoknya dikembalikan (lihat command pembayaran:kedaluwarsa).
    'batas_bayar_jam' => (int) env('MARKETPLACE_BATAS_BAYAR_JAM', 24),

    // Rekening penampung untuk metode transfer manual. Pembeli mentransfer
    // ke rekening ini lalu mengunggah bukti; Admin memverifikasi. Kalau
    // nomor rekening kosong, metode transfer manual tidak ditawarkan.
    'rekening' => [
        'bank' => env('MARKETPLACE_BANK', 'Bank BRI'),
        'nomor' => env('MARKETPLACE_NO_REKENING'),
        'atas_nama' => env('MARKETPLACE_ATAS_NAMA'),
    ],

    // Ukuran maksimal file bukti transfer (KB)
    'bukti_maks_kb' => 4096,

    // Batas jumlah baris produk berbeda di satu keranjang
    'maks_item_keranjang' => 30,

];
