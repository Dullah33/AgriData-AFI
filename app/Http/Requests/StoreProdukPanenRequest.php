<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdukPanenRequest extends FormRequest
{
    /**
     * Tentukan apakah user berhak melakukan request ini.
     * true = semua user yang sudah login (auth:sanctum) boleh.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk data produk panen.
     */
    public function rules(): array
    {
        return [
            'nama_produk' => ['required', 'string', 'max:255'],
            'kategori'    => ['required', 'string', 'max:100'],
            'deskripsi'   => ['nullable', 'string'],
            'harga'       => ['required', 'numeric', 'min:0'],
            'satuan'      => ['required', 'string', 'max:50'],
            'stok'        => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Pesan error custom (opsional, biar konsisten formatnya).
     */
    public function messages(): array
    {
        return [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'kategori.required'    => 'Kategori wajib diisi.',
            'harga.required'       => 'Harga wajib diisi.',
            'harga.numeric'        => 'Harga harus berupa angka.',
            'satuan.required'      => 'Satuan wajib diisi.',
            'stok.required'        => 'Stok wajib diisi.',
            'stok.integer'         => 'Stok harus berupa bilangan bulat.',
        ];
    }
}
