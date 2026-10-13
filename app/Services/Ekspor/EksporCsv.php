<?php

namespace App\Services\Ekspor;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Unduhan CSV yang di-stream baris demi baris, jadi data besar tidak
 * dimuat sekaligus ke memori. Dibuka langsung oleh Excel/Google Sheets.
 */
class EksporCsv
{
    /**
     * @param  iterable<array>  $baris  Boleh LazyCollection/generator
     */
    public function unduh(string $namaFile, array $judulKolom, iterable $baris): StreamedResponse
    {
        return response()->streamDownload(function () use ($judulKolom, $baris) {
            $keluaran = fopen('php://output', 'w');

            // BOM supaya huruf non-ASCII terbaca benar saat dibuka di Excel
            fwrite($keluaran, "\xEF\xBB\xBF");
            fputcsv($keluaran, $judulKolom);

            foreach ($baris as $kolom) {
                fputcsv($keluaran, array_map($this->amankan(...), $kolom));
            }

            fclose($keluaran);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Cegah CSV/formula injection: teks yang diawali = + - @ (atau tab/CR)
     * dijalankan sebagai rumus oleh Excel. Data seperti username atau nama
     * kelompok tani diisi pengguna, jadi diberi awalan ' agar tetap teks.
     * Angka (termasuk negatif) dibiarkan apa adanya.
     */
    public function amankan(mixed $nilai): mixed
    {
        if (! is_string($nilai) || $nilai === '' || is_numeric($nilai)) {
            return $nilai;
        }

        return in_array($nilai[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$nilai : $nilai;
    }
}
