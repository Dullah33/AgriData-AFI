<?php

namespace App\Http\Requests\Petani;

use App\Models\ProdukPanen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Tambah & ubah produk dari halaman petani. Kepemilikan produk dicek
 * lewat ProdukPanenPolicy di controller.
 */
class ProdukPanenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $aturan = [
            'nama_produk' => ['required', 'string', 'max:150'],
            'kategori'    => ['required', Rule::in(ProdukPanen::KATEGORI)],
            'deskripsi'   => ['required', 'string'],
            'harga'       => ['required', 'numeric', 'min:0'],
            'satuan'      => ['required', 'string', 'max:20'],
            'stok'        => ['required', 'integer', 'min:0'],
            'foto_produk' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        // Status hanya bisa diubah setelah produk dibuat
        if ($this->route('produk')) {
            $aturan['status'] = ['required', Rule::in(ProdukPanen::STATUS_TERLIHAT)];
        }

        return $aturan;
    }

    public function messages(): array
    {
        return [
            'nama_produk.required' => 'Nama produk harus diisi.',
            'kategori.required'    => 'Kategori harus dipilih.',
            'kategori.in'          => 'Kategori tidak dikenal.',
            'deskripsi.required'   => 'Deskripsi harus diisi.',
            'harga.required'       => 'Harga harus diisi.',
            'stok.required'        => 'Stok harus diisi.',
            'foto_produk.image'    => 'File harus berupa gambar.',
            'foto_produk.max'      => 'Ukuran foto maksimal 2MB.',
        ];
    }
}
