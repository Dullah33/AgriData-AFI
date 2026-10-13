<?php

namespace App\Http\Requests\Marketplace;

use App\Models\KeranjangItem;
use App\Models\ProdukPanen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Validasi tambah produk ke keranjang (tombol "Tambah ke keranjang" dan
 * "Beli sekarang"). Jumlah yang sudah ada di keranjang ikut dihitung,
 * supaya pembeli tidak bisa menambah sedikit demi sedikit sampai melebihi stok.
 */
class TambahKeranjangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jumlah' => ['required', 'integer', 'min:1', 'max:'.max(1, $this->produk()->stok)],
            'aksi' => ['nullable', 'in:keranjang,beli'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $produk = $this->produk();

                if (! $produk->bisaDibeli()) {
                    $validator->errors()->add('jumlah', 'Produk ini sedang tidak bisa dibeli.');

                    return;
                }

                $diKeranjang = $this->itemDiKeranjang()?->jumlah ?? 0;
                $sisaBoleh = $produk->stok - $diKeranjang;

                if ((int) $this->input('jumlah') > $sisaBoleh) {
                    $validator->errors()->add('jumlah', $sisaBoleh > 0
                        ? "Keranjang Anda sudah berisi {$diKeranjang} {$produk->satuan}. Maksimal bisa ditambah {$sisaBoleh} {$produk->satuan} lagi."
                        : "Seluruh stok yang tersedia ({$produk->stok} {$produk->satuan}) sudah ada di keranjang Anda.");
                }

                if (! $this->itemDiKeranjang()
                    && KeranjangItem::where('user_id', $this->user()->id)->count() >= config('marketplace.maks_item_keranjang', 30)) {
                    $validator->errors()->add('jumlah', 'Keranjang sudah penuh. Selesaikan checkout dulu sebelum menambah produk lain.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'jumlah.required' => 'Jumlah harus diisi.',
            'jumlah.integer' => 'Jumlah harus berupa angka bulat.',
            'jumlah.min' => 'Jumlah minimal 1.',
            'jumlah.max' => 'Jumlah melebihi stok yang tersedia (:max).',
        ];
    }

    public function produk(): ProdukPanen
    {
        return $this->route('produk');
    }

    public function itemDiKeranjang(): ?KeranjangItem
    {
        return KeranjangItem::where('user_id', $this->user()->id)
            ->where('produk_id', $this->produk()->id)
            ->first();
    }
}
