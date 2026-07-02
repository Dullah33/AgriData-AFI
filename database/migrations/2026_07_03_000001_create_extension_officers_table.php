<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel profil Petugas Penyuluh Pertanian, mengacu ke spesifikasi
     * tabel `extension_officers` BAB 4.2.13 dokumen modul sistem.
     *
     * Berbeda dengan Petani, akun Penyuluh TIDAK didaftarkan lewat form
     * registrasi publik — dibuat langsung oleh Admin sekaligus penetapan
     * wilayah binaan (lihat BAB 6.1 & 6.7). Karena itu tabel ini tidak
     * butuh kolom "menunggu verifikasi" terpisah; status aktif/nonaktif
     * cukup ditentukan lewat kolom `status`.
     *
     * Kolom `wilayah_binaan` sengaja disimpan sebagai string bebas
     * (nama desa/kecamatan), bukan foreign key ke tabel wilayah, karena
     * satu Penyuluh bisa membina beberapa desa sekaligus dan dokumen
     * modul tidak mensyaratkan relasi ketat di level database — cukup
     * dipakai untuk pencocokan teks saat menampilkan daftar petani
     * binaan pada halaman "Wilayah Binaan".
     */
    public function up(): void
    {
        Schema::create('extension_officers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nip', 30)->unique()->nullable();
            $table->string('wilayah_binaan', 150);
            $table->string('phone', 20)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extension_officers');
    }
};
