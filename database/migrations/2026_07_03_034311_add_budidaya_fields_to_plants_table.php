<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            // Kolom untuk Info Cards (Text)
            $table->text('budidaya_persiapan_lahan')->nullable();
            $table->text('budidaya_pemupukan')->nullable();
            $table->text('budidaya_irigasi')->nullable();
            
            // Kolom untuk Timeline Steps & Tips (JSON)
            $table->json('langkah_budidaya')->nullable(); 
            $table->json('tips_budidaya')->nullable();    
            
            // Kolom untuk CTA Section
            $table->string('cta_text')->nullable();
            $table->string('cta_link')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->dropColumn([
                'budidaya_persiapan_lahan',
                'budidaya_pemupukan',
                'budidaya_irigasi',
                'langkah_budidaya',
                'tips_budidaya',
                'cta_text',
                'cta_link'
            ]);
        });
    }
};