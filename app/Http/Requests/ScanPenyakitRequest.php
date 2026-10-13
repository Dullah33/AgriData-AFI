<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi foto untuk AI Scanner Penyakit Tanaman, dipakai bersama oleh
 * scanner Petani dan scanner User (Terbatas).
 *
 * Pemeriksaannya berlapis supaya file yang bukan gambar tidak lolos
 * walaupun ekstensinya diganti:
 *  - image + mimes + mimetypes: isi file dicek lewat magic bytes (finfo),
 *    bukan dari nama file. SVG otomatis ditolak oleh rule image.
 *  - extensions: ekstensi nama file juga harus cocok.
 *  - dimensions: file harus bisa dibaca sebagai gambar sungguhan dan
 *    resolusinya cukup untuk dianalisis.
 *  - max:5120: ukuran maksimal 5 MB.
 */
class ScanPenyakitRequest extends FormRequest
{
    public const MAKS_KB = 5120;

    // Pembatasan role sudah ditangani middleware "role:" di routes/web.php
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'foto_tanaman' => [
                'bail',     // berhenti di aturan pertama yang gagal, supaya pesannya tidak menumpuk
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'extensions:jpg,jpeg,png,webp',
                'max:'.self::MAKS_KB,
                'dimensions:min_width=200,min_height=200',
            ],
            // Hanya dipakai scanner Petani. Kepemilikan lahan dicek di controller.
            'lahan_id' => ['nullable', 'integer', 'exists:lahan,id'],
        ];
    }

    public function messages(): array
    {
        $format = 'Format foto harus JPG, PNG, atau WEBP.';

        return [
            'foto_tanaman.required'   => 'Foto tanaman wajib diunggah atau diambil dari kamera.',
            'foto_tanaman.uploaded'   => 'Foto gagal diunggah. Pastikan ukurannya tidak lebih dari 5 MB, lalu coba lagi.',
            'foto_tanaman.file'       => 'Foto gagal diunggah. Silakan coba lagi.',
            'foto_tanaman.image'      => 'File yang diunggah harus berupa gambar.',
            'foto_tanaman.mimes'      => $format,
            'foto_tanaman.mimetypes'  => $format,
            'foto_tanaman.extensions' => $format,
            'foto_tanaman.max'        => 'Ukuran foto maksimal 5 MB.',
            'foto_tanaman.dimensions' => 'Resolusi foto terlalu kecil. Gunakan foto minimal 200 × 200 piksel.',
            'lahan_id.integer'        => 'Lahan yang dipilih tidak valid.',
            'lahan_id.exists'         => 'Lahan yang dipilih tidak ditemukan.',
        ];
    }
}
