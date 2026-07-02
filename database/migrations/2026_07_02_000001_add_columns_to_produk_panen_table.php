<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Melengkapi tabel produk_panen sesuai spesifikasi `products` BAB 4.2.4:
     * kategori produk, satuan (kg/ton/ikat, dll.), dan status listing.
     */
    public function up(): void
    {
        Schema::table('produk_panen', function (Blueprint $table) {
            $table->string('kategori', 50)->nullable()->after('nama_produk');
            $table->string('satuan', 20)->default('kg')->after('harga');
            $table->enum('status', ['tersedia', 'habis', 'dihapus'])
                ->default('tersedia')->after('foto_produk');
        });
    }

    public function down(): void
    {
        Schema::table('produk_panen', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'satuan', 'status']);
        });
    }
};
