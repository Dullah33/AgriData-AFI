<?php

namespace App\Http\Requests\Marketplace;

use App\Models\KeranjangItem;
use Illuminate\Foundation\Http\FormRequest;

class UbahKeranjangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->item()->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'jumlah' => ['required', 'integer', 'min:1', 'max:'.max(1, (int) $this->item()->produk?->stok)],
        ];
    }

    public function messages(): array
    {
        $produk = $this->item()->produk;

        return [
            'jumlah.required' => 'Jumlah harus diisi.',
            'jumlah.integer' => 'Jumlah harus berupa angka bulat.',
            'jumlah.min' => 'Jumlah minimal 1. Gunakan tombol hapus untuk mengeluarkan produk.',
            'jumlah.max' => 'Stok '.($produk->nama_produk ?? 'produk').' tinggal :max '.($produk->satuan ?? '').'.',
        ];
    }

    public function item(): KeranjangItem
    {
        return $this->route('item');
    }
}
