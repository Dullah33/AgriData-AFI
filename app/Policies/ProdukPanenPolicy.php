<?php

namespace App\Policies;

use App\Models\ProdukPanen;
use App\Models\User;

/**
 * Hak akses produk panen, dipakai halaman petani dan API /api/produk-panen.
 * Hanya petani yang boleh menjual, dan hanya pemilik yang boleh mengubah.
 */
class ProdukPanenPolicy
{
    public function create(User $user): bool
    {
        return $user->isPetani();
    }

    public function update(User $user, ProdukPanen $produk): bool
    {
        return $user->isPetani() && $produk->petani_id === $user->id;
    }

    public function delete(User $user, ProdukPanen $produk): bool
    {
        return $this->update($user, $produk);
    }
}
