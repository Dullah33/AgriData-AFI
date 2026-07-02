<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Melengkapi tabel petani_profile dengan kolom yang ada di spesifikasi
     * tabel `farmers` pada dokumen modul sistem (BAB 4.2.2), tapi belum
     * dibuat saat migration awal: NIK, alamat lengkap, komoditas utama,
     * dan status verifikasi oleh Admin.
     */
    public function up(): void
    {
        Schema::table('petani_profile', function (Blueprint $table) {
            $table->string('nik', 16)->unique()->nullable()->after('user_id');
            $table->text('alamat')->nullable()->after('wilayah_id');
            $table->string('komoditas_utama', 100)->nullable()->after('nama_kelompok_tani');
            $table->string('foto_ktp', 255)->nullable()->after('komoditas_utama');
            $table->foreignId('verified_by')->nullable()->after('status_aktif')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petani_profile', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['nik', 'alamat', 'komoditas_utama', 'foto_ktp', 'verified_by']);
        });
    }
};