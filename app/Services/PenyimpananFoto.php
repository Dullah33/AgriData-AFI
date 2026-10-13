<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Simpan/ganti/hapus foto di disk public (foto produk, sampul artikel,
 * dokumentasi kunjungan). Sebelumnya pola "simpan baru lalu hapus lama"
 * ditulis ulang di beberapa controller.
 */
class PenyimpananFoto
{
    private const DISK = 'public';

    /**
     * Simpan foto baru; foto lama (kalau ada) dihapus setelah yang baru tersimpan.
     */
    public function ganti(UploadedFile $file, string $folder, ?string $lama = null): string
    {
        $baru = $file->store($folder, self::DISK);
        $this->hapus($lama);

        return $baru;
    }

    public function hapus(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
