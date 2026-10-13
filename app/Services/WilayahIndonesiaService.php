<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Daftar provinsi/kabupaten/kecamatan dari API publik EMSIFA. Datanya nyaris
 * tidak pernah berubah, jadi di-cache 7 hari; sebelumnya setiap pilihan di
 * dropdown halaman cuaca memanggil API eksternal lagi.
 */
class WilayahIndonesiaService
{
    private const BASE_URL = 'https://emsifa.github.io/api-wilayah-indonesia/api';

    private const LAMA_CACHE = 7 * 24 * 60 * 60;

    public function provinsi(): array
    {
        return $this->ambil('provinces');
    }

    public function kabupaten(string $kodeProvinsi): array
    {
        return $this->ambil("regencies/{$kodeProvinsi}");
    }

    public function kecamatan(string $kodeKabupaten): array
    {
        return $this->ambil("districts/{$kodeKabupaten}");
    }

    private function ambil(string $jalur): array
    {
        // Jalur hanya terdiri dari nama tetap + kode angka (dibatasi whereNumber di route)
        return Cache::remember("wilayah-id:{$jalur}", self::LAMA_CACHE, function () use ($jalur) {
            $respons = Http::timeout(10)->get(self::BASE_URL."/{$jalur}.json");

            if (! $respons->successful() || ! is_array($respons->json())) {
                throw new RuntimeException("Gagal memuat data wilayah ({$jalur}).");
            }

            return $respons->json();
        });
    }
}
