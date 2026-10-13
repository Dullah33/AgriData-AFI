<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        // SQLite (dipakai test suite lewat phpunit.xml) tidak mengenal
        // MODIFY COLUMN, jadi pakai schema builder yang membuat ulang tabel.
        if (DB::getDriverName() === 'sqlite') {
            $this->ubahEnumRole(['admin', 'petani', 'penyuluh', 'user']);

            return;
        }

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
        if (DB::getDriverName() === 'sqlite') {
            $this->ubahEnumRole(['admin', 'user']);

            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user') NOT NULL DEFAULT 'user'");
    }

    private function ubahEnumRole(array $roles): void
    {
        Schema::table('users', function (Blueprint $table) use ($roles) {
            $table->enum('role', $roles)->default('user')->change();
        });
    }
};