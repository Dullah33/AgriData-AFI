<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel jadwal & laporan kunjungan lapangan Penyuluh ke petani binaan,
     * mengacu ke spesifikasi tabel `field_visits` BAB 4.2.14 dokumen modul
     * sistem. Satu baris merepresentasikan satu kunjungan — baik yang baru
     * terjadwal (belum dilaksanakan) maupun yang sudah selesai dengan
     * laporan terisi (BAB 2A.6: "Jadwal Kunjungan Lapangan" dan
     * "Laporan Hasil Kunjungan" memakai data yang sama, dibedakan lewat
     * kolom status).
     */
    public function up(): void
    {
        Schema::create('field_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_officer_id')->constrained('extension_officers')->cascadeOnDelete();
            $table->foreignId('petani_profile_id')->constrained('petani_profile')->cascadeOnDelete();
            $table->date('tanggal_kunjungan');
            $table->text('catatan_persiapan')->nullable();
            $table->enum('status', ['terjadwal', 'selesai', 'batal'])->default('terjadwal');

            // Diisi setelah kunjungan dilaksanakan (Form Laporan Kunjungan)
            $table->text('kondisi_lahan')->nullable();
            $table->text('kendala_ditemukan')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->string('foto_dokumentasi', 255)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_visits');
    }
};
