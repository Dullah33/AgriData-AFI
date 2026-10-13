<?php

namespace App\Actions\Penyuluh;

use App\Models\ExtensionOfficer;
use Illuminate\Support\Facades\DB;

/**
 * Hapus profil penyuluh beserta akun user-nya dalam satu transaksi.
 */
class HapusAkunPenyuluh
{
    public function __invoke(ExtensionOfficer $penyuluh): void
    {
        DB::transaction(function () use ($penyuluh) {
            $user = $penyuluh->user;
            $penyuluh->delete();
            $user?->delete();
        });
    }
}
