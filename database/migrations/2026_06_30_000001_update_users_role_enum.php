<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Mengubah kolom enum `role` pada tabel users dari ['admin','user']
     * menjadi ['admin','petani','penyuluh','user'] sesuai dokumen modul
     * sistem (4 role, distributor dihapus dari ruang lingkup).
     */
    public function up(): void
    {
        // ALTER TABLE manual via raw SQL karena Laravel tidak punya
        // method bawaan untuk mengubah daftar nilai enum yang sudah ada.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'petani', 'penyuluh', 'user') NOT NULL DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // PENTING: sebelum rollback, pastikan tidak ada user dengan role
        // 'petani' atau 'penyuluh' di database, karena akan gagal/error
        // saat enum dikembalikan ke versi lama yang lebih sempit.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user') NOT NULL DEFAULT 'user'");
    }
};