<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ExtensionOfficer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Helper bersama untuk controller di area Penyuluh: mengambil profil
 * penyuluh yang login dan memastikan data yang dibuka miliknya sendiri.
 */
trait MembutuhkanPenyuluh
{
    // Null kalau akun penyuluh belum didaftarkan profilnya oleh admin
    protected function penyuluhAtauNull(): ?ExtensionOfficer
    {
        return Auth::user()->extensionOfficer;
    }

    protected function penyuluh(): ExtensionOfficer
    {
        $officer = $this->penyuluhAtauNull();
        abort_if(! $officer, 403, 'Profil Penyuluh belum terdaftar. Hubungi Admin.');

        return $officer;
    }

    // Kunjungan, pelatihan, dan laporan bulanan punya kolom extension_officer_id
    protected function pastikanMilikPenyuluh(Model $data): ExtensionOfficer
    {
        $officer = $this->penyuluh();
        abort_if($data->extension_officer_id !== $officer->id, 403);

        return $officer;
    }
}
