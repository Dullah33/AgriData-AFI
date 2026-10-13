<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keranjang belanja pembeli. Disimpan di database (bukan session)
     * supaya isinya tidak hilang saat session kedaluwarsa, pindah perangkat,
     * atau server di-redeploy. Keranjang TIDAK mengunci stok; stok baru
     * dikurangi saat checkout.
     */
    public function up(): void
    {
        Schema::create('keranjang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk_panen')->cascadeOnDelete();
            $table->unsignedInteger('jumlah');
            $table->timestamps();

            // Satu produk cukup satu baris per pembeli; jumlahnya yang ditambah
            $table->unique(['user_id', 'produk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keranjang');
    }
};
