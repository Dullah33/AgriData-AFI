<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * weather_data sekarang menyimpan cuaca terakhir per lahan (diisi job
     * pembaruan harian). Baris lama yang berbasis nama wilayah tetap boleh
     * ada, karena itu lahan_id dibuat nullable.
     */
    public function up(): void
    {
        Schema::table('weather_data', function (Blueprint $table) {
            $table->foreignId('lahan_id')->nullable()->unique()->after('id')
                ->constrained('lahan')->cascadeOnDelete();
            $table->string('sumber', 20)->nullable()->after('kondisi_cuaca');
            $table->timestamp('diambil_at')->nullable()->after('sumber');
        });
    }

    public function down(): void
    {
        Schema::table('weather_data', function (Blueprint $table) {
            $table->dropForeign(['lahan_id']);
            $table->dropUnique(['lahan_id']);
            $table->dropColumn(['lahan_id', 'sumber', 'diambil_at']);
        });
    }
};
