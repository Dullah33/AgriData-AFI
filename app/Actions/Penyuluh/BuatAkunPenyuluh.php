<?php

namespace App\Actions\Penyuluh;

use App\Models\ExtensionOfficer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Akun Penyuluh tidak lewat registrasi publik (BAB 2.2.2 & 6.1): admin
 * membuat akun user sekaligus profil penyuluh dan wilayah binaannya.
 * Keduanya dibuat dalam satu transaksi supaya tidak ada user penyuluh
 * tanpa profil kalau salah satu langkah gagal.
 */
class BuatAkunPenyuluh
{
    public function __invoke(array $data, User $admin): ExtensionOfficer
    {
        return DB::transaction(function () use ($data, $admin) {
            $user = User::create([
                'username' => $data['username'],
                'email'    => $data['email'],
                'password' => $data['password'],
                'role'     => 'penyuluh',
            ]);

            return $user->extensionOfficer()->create([
                'nip'            => $data['nip'] ?? null,
                'wilayah_binaan' => $data['wilayah_binaan'],
                'phone'          => $data['phone'] ?? null,
                'status'         => 'aktif',
                'assigned_by'    => $admin->id,
            ]);
        });
    }
}
