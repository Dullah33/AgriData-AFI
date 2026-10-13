<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - pembayaran_id : tagihan yang menaungi transaksi ini. Null untuk
     *   transaksi lama yang dibuat sebelum ada fitur pembayaran.
     * - harga_satuan  : harga produk saat dibeli. Disimpan supaya riwayat
     *   tidak ikut berubah kalau petani mengubah harga di kemudian hari.
     * - alasan_batal & stempel waktu tiap perubahan status.
     */
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreignId('pembayaran_id')->nullable()->after('produk_id')->constrained('pembayaran')->nullOnDelete();
            $table->decimal('harga_satuan', 12, 2)->nullable()->after('jumlah');
            $table->text('alasan_batal')->nullable();
            $table->timestamp('diproses_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamp('dibatalkan_at')->nullable();
        });

        // Isi harga satuan untuk transaksi lama dari total / jumlah
        DB::table('transaksi')
            ->whereNull('harga_satuan')
            ->where('jumlah', '>', 0)
            ->update(['harga_satuan' => DB::raw('total_harga / jumlah')]);
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['pembayaran_id']);
            $table->dropColumn(['pembayaran_id', 'harga_satuan', 'alasan_batal', 'diproses_at', 'selesai_at', 'dibatalkan_at']);
        });
    }
};
