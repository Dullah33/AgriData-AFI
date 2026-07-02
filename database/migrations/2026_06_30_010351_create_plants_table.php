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
        Schema::create('plants', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique()->nullable();
            $table->string('nama')->unique();
            $table->string('gambar')->nullable();
            
            // Karakteristik Ideal (untuk display)
            $table->string('suhu_ideal', 50)->nullable();
            $table->decimal('min_suhu', 5, 2)->nullable();
            $table->decimal('max_suhu', 5, 2)->nullable();
            
            $table->string('kelembapan_ideal', 50)->nullable();
            $table->decimal('min_kelembaban', 5, 2)->nullable();
            $table->decimal('max_kelembaban', 5, 2)->nullable();
            
            $table->string('curah_hujan_ideal', 50)->nullable();
            $table->string('musim', 100)->nullable();
            $table->string('jenis_tanah', 100)->nullable();
            
            // Rekomendasi berdasarkan cuaca
            $table->text('status_cuaca_hujan')->nullable();
            $table->text('status_cuaca_panas')->nullable();
            $table->text('status_curah_hujan_tinggi')->nullable();
            $table->text('status_curah_hujan_rendah')->nullable();
            
            // Informasi tambahan
            $table->text('deskripsi')->nullable();
            $table->text('keunggulan')->nullable();
            $table->string('durasi_panen', 50)->nullable();
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index('nama');
            $table->index('musim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plants');
    }
};