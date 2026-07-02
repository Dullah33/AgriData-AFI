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
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id();
            
            // Lokasi
            $table->string('provinsi')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('nama_lokasi')->nullable();
            
            // Koordinat
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            // Data cuaca real-time
            $table->decimal('suhu', 5, 2)->nullable();
            $table->decimal('kelembaban', 5, 2)->nullable();
            $table->decimal('curah_hujan', 6, 2)->nullable();
            $table->decimal('kecepatan_angin', 5, 2)->nullable();
            $table->string('kondisi_cuaca', 100)->nullable();
            
            // Data historis dan forecast (JSON)
            $table->json('historical_data')->nullable();
            $table->json('forecast_data')->nullable();
            $table->json('weather_data')->nullable();
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['provinsi', 'kabupaten', 'kecamatan']);
            $table->index(['latitude', 'longitude']);
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_data');
    }
};