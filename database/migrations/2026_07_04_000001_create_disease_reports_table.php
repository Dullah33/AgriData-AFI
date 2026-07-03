<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel laporan deteksi penyakit tanaman, mengacu ke spesifikasi tabel
     * `disease_reports` BAB 4.2.8 dokumen modul sistem. Diisi otomatis
     * setiap kali AI Scanner (Petani/User) selesai menganalisis foto
     * tanaman, lalu dipakai juga oleh:
     *  - Admin: Dashboard Pemetaan Penyakit (BAB 2A.2)
     *  - Penyuluh: Deteksi Penyakit di Wilayah Binaan + rekomendasi tindak
     *    lanjut (BAB 2.7 "Tindak Lanjut Deteksi Penyakit")
     *
     * Kolom tambahan di luar spesifikasi dokumen (lahan_id, wilayah_id,
     * tingkat_risiko, status, rekomendasi_tindak_lanjut, ditinjau_oleh,
     * ditinjau_at) diperlukan supaya alur tindak lanjut Penyuluh (yang
     * memang diminta di BAB 2.7 & KF-23) bisa berjalan tanpa tabel
     * terpisah.
     */
    public function up(): void
    {
        Schema::create('disease_reports', function (Blueprint $table) {
            $table->id();

            // Pelapor: bisa Petani atau User (AI Scanner Terbatas) — BAB 3.2 & 3.3
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Opsional: lahan spesifik milik petani, jika dipilih saat scan
            $table->foreignId('lahan_id')->nullable()->constrained('lahan')->nullOnDelete();

            // Opsional: dicocokkan ke ensiklopedia tanaman jika nama tanaman dikenali
            $table->foreignId('plant_id')->nullable()->constrained('plants')->nullOnDelete();

            // Untuk pemetaan (Dashboard Pemetaan Penyakit) & filter wilayah binaan Penyuluh
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayah')->nullOnDelete();

            $table->string('nama_penyakit', 150);
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->string('foto_tanaman', 255);
            $table->text('gejala')->nullable();
            $table->text('penyebab')->nullable();
            $table->text('penanganan')->nullable();

            // Indikator/faktor pemicu penyakit — BAB 2.5.1
            $table->enum('tingkat_risiko', ['rendah', 'sedang', 'tinggi'])->default('sedang');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Status tindak lanjut oleh Penyuluh (BAB 2.7)
            $table->enum('status', ['baru', 'ditinjau', 'ditindaklanjuti'])->default('baru');
            $table->text('rekomendasi_tindak_lanjut')->nullable();
            $table->foreignId('ditinjau_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditinjau_at')->nullable();

            $table->timestamps();

            $table->index('nama_penyakit');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disease_reports');
    }
};
