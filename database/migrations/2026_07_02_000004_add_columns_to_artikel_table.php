<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Melengkapi tabel artikel sesuai spesifikasi `articles` BAB 4.2.9:
     * kategori artikel dan status publikasi (draft/published).
     */
    public function up(): void
    {
        Schema::table('artikel', function (Blueprint $table) {
            $table->string('kategori', 50)->nullable()->after('judul');
            $table->enum('status', ['draft', 'published'])->default('draft')->after('foto_sampul');
            $table->timestamp('published_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('artikel', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'status', 'published_at']);
        });
    }
};
