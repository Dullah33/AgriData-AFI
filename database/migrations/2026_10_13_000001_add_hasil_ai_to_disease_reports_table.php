<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyimpan respons JSON asli dari AI (Gemini) untuk setiap laporan.
     *
     * Kolom nama_penyakit, gejala, penanganan, dll tetap diisi seperti biasa
     * supaya tampilan Petani, Penyuluh, dan Admin tidak perlu berubah. Kolom
     * ini berguna untuk audit (model apa yang menjawab, apa jawaban aslinya)
     * dan untuk data tambahan seperti nama tanaman yang dikenali AI.
     */
    public function up(): void
    {
        Schema::table('disease_reports', function (Blueprint $table) {
            $table->json('hasil_ai')->nullable()->after('tingkat_risiko');
        });
    }

    public function down(): void
    {
        Schema::table('disease_reports', function (Blueprint $table) {
            $table->dropColumn('hasil_ai');
        });
    }
};
