<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Melengkapi tabel ulasan_rating sesuai spesifikasi `reviews` BAB 4.2.6:
     * status untuk keperluan moderasi oleh Admin.
     */
    public function up(): void
    {
        Schema::table('ulasan_rating', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'dihapus'])->default('aktif')->after('komentar');
        });
    }

    public function down(): void
    {
        Schema::table('ulasan_rating', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
