<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdukPanenResource extends JsonResource
{
    /**
     * Transformasi model menjadi array JSON yang konsisten.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nama_produk' => strtoupper($this->nama_produk),
            'kategori'    => $this->kategori,
            'deskripsi'   => $this->deskripsi,
            'harga'       => 'Rp ' . number_format($this->harga, 0, ',', '.'),
            'stok'        => $this->stok . ' ' . $this->satuan,
            'status'      => $this->status,
            'petani'      => $this->whenLoaded('petani', fn () => $this->petani->username),
            'created_at'  => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
