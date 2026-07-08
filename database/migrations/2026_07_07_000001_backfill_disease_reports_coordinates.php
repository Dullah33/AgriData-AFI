<?php

use App\Models\DiseaseReport;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Isi ulang latitude/longitude untuk laporan penyakit lama yang dibuat
     * sebelum bug "koordinat tidak pernah diisi" diperbaiki di
     * Petani\DeteksiPenyakitController. Tanpa ini, laporan-laporan lama
     * (termasuk yang sudah ditest) tidak akan pernah muncul di peta
     * meskipun kode baru sudah benar, karena baris datanya sendiri
     * memang kosong.
     */
    public function up(): void
    {
        DiseaseReport::whereNull('latitude')
            ->orWhereNull('longitude')
            ->with(['lahan', 'wilayah'])
            ->get()
            ->each(function (DiseaseReport $laporan) {
                $koordinat = $laporan->lahan?->centroid();

                if (! $koordinat && $laporan->wilayah) {
                    $koordinat = [$laporan->wilayah->latitude, $laporan->wilayah->longitude];
                }

                if ($koordinat) {
                    $laporan->update([
                        'latitude'  => $koordinat[0],
                        'longitude' => $koordinat[1],
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Sengaja tidak dikembalikan ke NULL — backfill data lama tidak perlu di-rollback.
    }
};
