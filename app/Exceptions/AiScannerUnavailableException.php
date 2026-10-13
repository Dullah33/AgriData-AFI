<?php

namespace App\Exceptions;

/**
 * Dilempar saat layanan AI pendeteksi penyakit tidak bisa dipakai (API key
 * belum diatur, kuota habis, server Gemini sibuk, koneksi putus, dll).
 *
 * Pesannya ditujukan untuk pengguna, jadi jangan isi dengan detail teknis.
 * Detail teknis cukup masuk ke log lewat exception sebelumnya ($previous).
 */
class AiScannerUnavailableException extends \RuntimeException
{
    //
}
