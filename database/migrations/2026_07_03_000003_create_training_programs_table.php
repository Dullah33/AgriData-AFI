<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel kegiatan pelatihan/penyuluhan kelompok tani yang diselenggarakan
     * Penyuluh, mengacu ke spesifikasi tabel `training_programs` BAB 4.2.15
     * dokumen modul sistem.
     */
    public function up(): void
    {
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_officer_id')->constrained('extension_officers')->cascadeOnDelete();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('lokasi', 150);
            $table->date('tanggal_pelaksanaan');
            $table->unsignedInteger('jumlah_peserta')->nullable();
            $table->enum('status', ['terjadwal', 'selesai', 'batal'])->default('terjadwal');
            $table->string('foto_dokumentasi', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_programs');
    }
};
