<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Titik koordinat lahan untuk mengambil data cuaca. Diisi otomatis dari
     * titik tengah poligon yang digambar petani (lihat Lahan::booted), jadi
     * tidak perlu dihitung ulang setiap kali cuaca diminta.
     */
    public function up(): void
    {
        Schema::table('lahan', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('koordinat_poligon');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        // Isi koordinat untuk lahan lama yang sudah punya poligon
        DB::table('lahan')->whereNotNull('koordinat_poligon')->orderBy('id')
            ->each(function ($lahan) {
                $titik = json_decode($lahan->koordinat_poligon, true);

                if (! is_array($titik) || count($titik) < 3) {
                    return;
                }

                DB::table('lahan')->where('id', $lahan->id)->update([
                    'latitude' => round(collect($titik)->avg(fn ($p) => $p[0]), 7),
                    'longitude' => round(collect($titik)->avg(fn ($p) => $p[1]), 7),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('lahan', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
