<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * 4 akun role sesuai dokumen modul sistem v1.3.0 (distributor dihapus).
     */
    public function run(): void
    {
        // 1. Admin Dinas Pertanian
        User::create([
            'username' => 'admin_agri',
            'email'    => 'admin@agridata.com',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
        ]);

        // 2. Petani
        User::create([
            'username' => 'petani01',
            'email'    => 'petani@agridata.com',
            'password' => Hash::make('petani123'),
            'role'     => 'petani',
        ]);

        // 3. Petugas Penyuluh Pertanian (PPL)
        User::create([
            'username' => 'penyuluh01',
            'email'    => 'penyuluh@agridata.com',
            'password' => Hash::make('password'),
            'role'     => 'penyuluh',
        ]);

        // 4. User Umum (Masyarakat)
        User::create([
            'username' => 'user01',
            'email'    => 'user@agridata.com',
            'password' => Hash::make('user123'),
            'role'     => 'user',
        ]);
    }
}