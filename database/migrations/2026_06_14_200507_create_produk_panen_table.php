<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produk_panen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petani_id')->constrained('users');
            $table->string('nama_produk', 150);
            $table->text('deskripsi');
            $table->decimal('harga', 12, 2);
            $table->integer('stok');
            $table->string('foto_produk', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk_panen');
    }
};
