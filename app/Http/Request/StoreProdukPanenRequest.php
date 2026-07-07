<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdukPanenRequest extends FormRequest
{
    /**
     * Diizinkan untuk semua user yang sudah login (dicek via middleware auth:sanctum).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk create & update produk panen.
     */
    public function rules(): array
    {
        return [
            'nama_produk' => 'required|string|max:150',
            'kategori'    => 'nullable|string|max:50',
            'deskripsi'   => 'required|string',
            'harga'       => 'required|numeric|min:0',
            'satuan'      => 'required|string|max:20',
            'stok'        => 'required|integer|min:0',
        ];
    }
}
