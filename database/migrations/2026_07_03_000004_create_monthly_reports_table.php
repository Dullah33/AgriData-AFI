<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel Laporan Kinerja Bulanan Penyuluh (BAB 2.7 & BAB 2A.6). Tabel ini
     * tidak eksplisit dispesifikasikan strukturnya di BAB 4, tetapi halaman
     * "Laporan Kinerja Bulanan" disebut jelas sebagai bagian tampilan wajib
     * Penyuluh — jadi ditambahkan mengikuti pola tabel Penyuluh lainnya.
     *
     * Ringkasan (jumlah kunjungan, jumlah pelatihan) dihitung otomatis dari
     * relasi field_visits & training_programs pada bulan terkait lewat
     * accessor di model, bukan disimpan manual, supaya datanya selalu akurat.
     */
    public function up(): void
    {
        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_officer_id')->constrained('extension_officers')->cascadeOnDelete();
            $table->unsignedSmallInteger('bulan'); // 1-12
            $table->unsignedSmallInteger('tahun');
            $table->text('ringkasan_kegiatan');
            $table->text('kendala')->nullable();
            $table->text('rencana_tindak_lanjut')->nullable();
            $table->enum('status', ['draft', 'terkirim'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['extension_officer_id', 'bulan', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_reports');
    }
};
