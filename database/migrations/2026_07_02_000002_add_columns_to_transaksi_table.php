<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Melengkapi tabel transaksi sesuai spesifikasi `orders` BAB 4.2.5:
     * alamat pengiriman dan catatan pembeli.
     */
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->text('alamat_pengiriman')->nullable()->after('status_transaksi');
            $table->text('catatan')->nullable()->after('alamat_pengiriman');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn(['alamat_pengiriman', 'catatan']);
        });
    }
};
