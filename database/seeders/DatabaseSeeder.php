<?php

namespace Database\Seeders;

use App\Models\PetaniProfile;
use App\Models\User;
use App\Models\Wilayah;
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
        $admin = User::create([
            'username' => 'admin_agri',
            'email'    => 'admin@agridata.com',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
        ]);

        // 2. Petani
        $petani = User::create([
            'username' => 'petani01',
            'email'    => 'petani@agridata.com',
            'password' => Hash::make('petani123'),
            'role'     => 'petani',
        ]);

        // 3. Petugas Penyuluh Pertanian (PPL)
        User::create([
            'username' => 'penyuluh01',
            'email'    => 'penyuluh@agridata.com',
            'password' => Hash::make('penyuluh123'),
            'role'     => 'penyuluh',
        ]);

        // 4. User Umum (Masyarakat)
        User::create([
            'username' => 'user01',
            'email'    => 'user@agridata.com',
            'password' => Hash::make('user123'),
            'role'     => 'user',
        ]);

        // Wilayah contoh (dibutuhkan sebagai FK oleh petani_profile)
        $wilayah = Wilayah::create([
            'nama_wilayah' => 'Kecamatan Madiun',
            'latitude'     => -7.6298,
            'longitude'    => 111.5239,
        ]);

        // Profil petani untuk akun petani01 di atas, supaya fitur yang
        // bergantung pada petani_profile (Lahan, dsb.) langsung bisa dites.
        // verified_by sengaja NULL — biar bisa dites via halaman verifikasi Admin.
        PetaniProfile::create([
            'user_id'            => $petani->id,
            'wilayah_id'         => $wilayah->id,
            'nama_kelompok_tani' => 'Kelompok Tani Sumber Makmur',
            'luas_lahan'         => 0.5,
            'status_aktif'       => true,
        ]);
    }
}