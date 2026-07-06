<?php

namespace Database\Seeders;

use App\Models\ExtensionOfficer;
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
        $admin = User::firstOrCreate(
            ['username' => 'admin_agri'],
            [
                'email'    => 'admin@agridata.com',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]
        );

        // 2. Petani
        $petani = User::firstOrCreate(
            ['username' => 'petani01'],
            [
                'email'    => 'petani@agridata.com',
                'password' => Hash::make('petani123'),
                'role'     => 'petani',
            ]
        );

        // 3. Petugas Penyuluh Pertanian (PPL)
        $penyuluh = User::firstOrCreate(
            ['username' => 'penyuluh01'],
            [
                'email'    => 'penyuluh@agridata.com',
                'password' => Hash::make('penyuluh123'),
                'role'     => 'penyuluh',
            ]
        );

        // 4. User Umum (Masyarakat)
        User::firstOrCreate(
            ['username' => 'user01'],
            [
                'email'    => 'user@agridata.com',
                'password' => Hash::make('user123'),
                'role'     => 'user',
            ]
        );

        // Wilayah contoh (dibutuhkan sebagai FK oleh petani_profile)
        $wilayah = Wilayah::firstOrCreate(
            ['nama_wilayah' => 'Kecamatan Madiun'],
            [
                'latitude'     => -7.6298,
                'longitude'    => 111.5239,
            ]
        );

        // Profil petani untuk akun petani01 di atas, supaya fitur yang
        // bergantung pada petani_profile (Lahan, dsb.) langsung bisa dites.
        // verified_by sengaja NULL — biar bisa dites via halaman verifikasi Admin.
        PetaniProfile::updateOrCreate(
            ['user_id' => $petani->id],
            [
                'wilayah_id'         => $wilayah->id,
                'nama_kelompok_tani' => 'Kelompok Tani Sumber Makmur',
                'luas_lahan'         => 0.5,
                'status_aktif'       => true,
            ]
        );

        // Profil penyuluh untuk akun penyuluh01, wilayah_binaan sengaja
        // disamakan dengan nama_wilayah di atas supaya relasi petaniBinaan()
        // (pencocokan lewat nama wilayah) langsung punya data untuk dites.
        ExtensionOfficer::updateOrCreate(
            ['user_id' => $penyuluh->id],
            [
                'nip'            => '198501012024031001',
                'wilayah_binaan' => $wilayah->nama_wilayah,
                'phone'          => '081234567890',
                'status'         => 'aktif',
                'assigned_by'    => $admin->id,
            ]
        );

        // Ensiklopedia tanaman (Panduan Budidaya) — sebelumnya PlantSeeder
        // tidak pernah dipanggil dari sini, jadi php artisan migrate:fresh
        // --seed tidak akan pernah mengisi tabel plants sama sekali.
        $this->call([
            PlantSeeder::class,
        ]);
    }
}