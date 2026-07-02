<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel lahan per petani, mengacu ke spesifikasi tabel `lands` BAB 4.2.3
     * dokumen modul sistem. Satu petani (petani_profile) bisa punya banyak
     * lahan (relasi 1-ke-banyak), berbeda dengan kolom luas_lahan di
     * petani_profile yang sifatnya agregat/ringkasan.
     *
     * Kolom tanaman_aktif sengaja disimpan sebagai string bebas (bukan FK
     * ke tabel plants) karena ensiklopedia tanaman (tabel plants) masih
     * dikerjakan terpisah — akan disambungkan sebagai foreign key nanti
     * begitu tabel plants tersedia.
     */
    public function up(): void
    {
        Schema::create('lahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petani_profile_id')->constrained('petani_profile')->onDelete('cascade');
            $table->string('nama_lahan', 100);
            $table->json('koordinat_poligon')->nullable();
            $table->decimal('luas_ha', 8, 2);
            $table->string('jenis_tanah', 50)->nullable();
            $table->string('tanaman_aktif', 100)->nullable();
            $table->date('tanggal_tanam')->nullable();
            $table->date('perkiraan_panen')->nullable();
            $table->enum('status', ['aktif', 'bera', 'panen'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lahan');
    }
};
