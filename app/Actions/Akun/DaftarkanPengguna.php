<?php

namespace App\Actions\Akun;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Pendaftaran akun publik, dipakai form register web dan POST /api/register.
 *
 * Role selalu 'user'. Akun petani diverifikasi admin dan akun penyuluh
 * dibuat langsung oleh admin, jadi pendaftar tidak boleh memilih role
 * sendiri (sebelumnya API menerima role=admin dari request).
 */
class DaftarkanPengguna
{
    public function __invoke(string $email, string $password, ?string $username = null): User
    {
        return User::create([
            'username' => $username ?? $this->usernameDariEmail($email),
            'email' => $email,
            // Di-hash otomatis oleh cast 'hashed' di model User
            'password' => $password,
            'role' => 'user',
        ]);
    }

    private function usernameDariEmail(string $email): string
    {
        $dasar = Str::limit(Str::before($email, '@'), 40, '');
        $username = $dasar;

        for ($n = 1; User::where('username', $username)->exists(); $n++) {
            $username = $dasar.$n;
        }

        return $username;
    }
}
