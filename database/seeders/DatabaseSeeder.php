<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Akun Admin
        User::create([
            'username' => 'admin_agri',
            'email'    => 'admin@agridata.com',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
        ]);

        // 2. Buat Akun User Biasa
        User::create([
            'username' => 'petani01',
            'email'    => 'petani@agridata.com',
            'password' => Hash::make('user123'),
            'role'     => 'user',
        ]);
        
        // 3. Buat Akun User Kedua
        User::create([
            'username' => 'budi_tani',
            'email'    => 'budi@agridata.com',
            'password' => Hash::make('password'),
            'role'     => 'user',
        ]);
    }
}