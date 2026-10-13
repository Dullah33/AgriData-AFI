<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tagihan pembayaran. Satu checkout menghasilkan satu pembayaran yang
     * menaungi beberapa baris transaksi (satu baris per produk).
     *
     * Kolom status sengaja string, bukan enum, supaya status baru bisa
     * ditambah tanpa ALTER TABLE yang berbeda-beda per database.
     * Daftar status ada di App\Models\Pembayaran.
     */
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->string('metode', 20);
            $table->decimal('jumlah', 12, 2);
            $table->string('status', 30)->default('menunggu_pembayaran')->index();
            $table->timestamp('batas_bayar')->nullable();

            // Transfer manual
            $table->string('bukti_transfer')->nullable();
            $table->timestamp('bukti_diunggah_at')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_at')->nullable();
            $table->text('catatan_verifikasi')->nullable();

            // Payment gateway (Midtrans)
            $table->string('gateway_order_id', 64)->nullable()->unique();
            $table->string('snap_token')->nullable();
            $table->string('gateway_transaction_id', 64)->nullable();
            $table->json('gateway_payload')->nullable();

            $table->timestamp('dibayar_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
