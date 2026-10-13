<?php

namespace App\Services;

use App\Models\DiseaseReport;
use App\Models\Lahan;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Data untuk halaman admin "Peta Lahan, Wilayah & Penyakit": marker wilayah,
 * poligon lahan beserta laporan penyakitnya, titik heatmap, tren bulanan,
 * dan peringatan dini wilayah rentan.
 */
class PetaPenyakitService
{
    // Wilayah dianggap rentan kalau ada >= 5 laporan dalam 30 hari terakhir
    public const AMBANG_RENTAN = 5;

    private const TINGKAT_RISIKO = ['tinggi' => 3, 'sedang' => 2, 'rendah' => 1];

    private const BOBOT_HEATMAP = ['tinggi' => 1.0, 'sedang' => 0.6, 'rendah' => 0.3];

    /**
     * Filter dari query string, dibersihkan tanpa melempar error validasi
     * (form filter memakai GET; redirect balik bisa berputar di URL yang sama).
     */
    public function filterDari(Request $request): array
    {
        $tahun = $request->integer('tahun', now()->year);
        $penyakit = $request->query('nama_penyakit');
        $risiko = $request->query('tingkat_risiko');

        return [
            'tahun'          => $tahun >= 2000 && $tahun <= now()->year + 1 ? $tahun : now()->year,
            'nama_penyakit'  => is_string($penyakit) && $penyakit !== '' ? mb_substr($penyakit, 0, 150) : null,
            'tingkat_risiko' => is_string($risiko) && isset(self::TINGKAT_RISIKO[$risiko]) ? $risiko : null,
            'wilayah_id'     => $request->integer('wilayah_id') ?: null,
        ];
    }

    /**
     * Laporan penyakit yang sudah kena filter (tanpa eager load; tambahkan
     * with() sesuai kebutuhan pemanggil).
     */
    public function laporan(array $filter): Builder
    {
        return DiseaseReport::query()
            ->whereYear('created_at', $filter['tahun'])
            ->when($filter['nama_penyakit'], fn ($q, $nama) => $q->where('nama_penyakit', $nama))
            ->when($filter['tingkat_risiko'], fn ($q, $risiko) => $q->where('tingkat_risiko', $risiko))
            ->when($filter['wilayah_id'], fn ($q, $id) => $q->where('wilayah_id', $id));
    }

    /**
     * Semua wilayah beserta jumlah petani dan jumlah laporan 30 hari terakhir,
     * dalam satu query. Sebelumnya laporan dihitung dengan satu query COUNT
     * per wilayah di dalam perulangan.
     */
    public function wilayahDenganStatistik(): Collection
    {
        return Wilayah::withCount([
            'petaniProfiles',
            'diseaseReports as laporan_30_hari' => fn ($q) => $q->where('created_at', '>=', now()->subDays(30)),
        ])->orderBy('nama_wilayah')->get();
    }

    public function markerWilayah(Collection $wilayah): Collection
    {
        return $wilayah
            ->filter(fn (Wilayah $w) => $w->latitude && $w->longitude)
            ->map(fn (Wilayah $w) => [
                'nama_wilayah'  => $w->nama_wilayah,
                'latitude'      => $w->latitude,
                'longitude'     => $w->longitude,
                'jumlah_petani' => $w->petani_profiles_count,
            ])->values();
    }

    public function wilayahRentan(Collection $wilayah): Collection
    {
        return $wilayah
            ->filter(fn (Wilayah $w) => $w->laporan_30_hari >= self::AMBANG_RENTAN)
            ->sortByDesc('laporan_30_hari')
            ->map(fn (Wilayah $w) => ['wilayah' => $w->nama_wilayah, 'jumlah' => $w->laporan_30_hari])
            ->values();
    }

    /**
     * Poligon lahan, masing-masing dengan laporan penyakit yang terkait,
     * supaya popup peta langsung menunjukkan pemilik dan penyakitnya.
     */
    public function poligonLahan(array $filter): Collection
    {
        return Lahan::query()
            ->whereNotNull('koordinat_poligon')
            ->with([
                'petaniProfile.user:id,username',
                'diseaseReports' => fn ($q) => $q
                    ->select(['id', 'lahan_id', 'nama_penyakit', 'tingkat_risiko', 'created_at'])
                    ->whereYear('created_at', $filter['tahun'])
                    ->when($filter['nama_penyakit'], fn ($q, $nama) => $q->where('nama_penyakit', $nama))
                    ->when($filter['tingkat_risiko'], fn ($q, $risiko) => $q->where('tingkat_risiko', $risiko)),
            ])
            ->get()
            ->filter(fn (Lahan $l) => is_array($l->koordinat_poligon) && count($l->koordinat_poligon) >= 3)
            ->map(fn (Lahan $l) => [
                'nama_lahan'       => $l->nama_lahan,
                'petani'           => $l->petaniProfile?->user?->username ?? '-',
                'tanaman_aktif'    => $l->tanaman_aktif,
                'luas_ha'          => $l->luas_ha,
                'status'           => $l->status,
                'koordinat'        => $l->koordinat_poligon,
                'risiko_tertinggi' => $l->diseaseReports->sortByDesc(fn ($d) => self::TINGKAT_RISIKO[$d->tingkat_risiko] ?? 0)->first()?->tingkat_risiko,
                'laporan_penyakit' => $l->diseaseReports->map(fn ($d) => [
                    'nama_penyakit'  => $d->nama_penyakit,
                    'tingkat_risiko' => $d->tingkat_risiko,
                    'tanggal'        => $d->created_at->format('d M Y'),
                ])->values(),
            ])->values();
    }

    /**
     * Laporan yang tidak terkait lahan (titik tengah wilayah) sebagai marker.
     * Hanya kolom yang dipakai peta yang diambil.
     */
    public function titikTanpaLahan(array $filter): Collection
    {
        return $this->laporan($filter)
            ->whereNull('lahan_id')
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->get(['nama_penyakit', 'tingkat_risiko', 'latitude', 'longitude']);
    }

    /**
     * Titik heatmap [lat, lng, bobot] dari semua laporan yang punya koordinat.
     */
    public function titikHeatmap(array $filter): Collection
    {
        return $this->laporan($filter)
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->toBase()
            ->get(['latitude', 'longitude', 'tingkat_risiko'])
            ->map(fn ($d) => [(float) $d->latitude, (float) $d->longitude, self::BOBOT_HEATMAP[$d->tingkat_risiko] ?? 0.3]);
    }

    /**
     * Jumlah laporan per bulan (indeks 0 = Januari) untuk grafik tren.
     * Ekspresi bulan disesuaikan dengan driver database: MONTH() tidak ada
     * di SQLite, jadi query lama error 500 di lingkungan test/dev.
     * Ekspresinya teks tetap tanpa input pengguna, jadi aman dari SQL injection.
     */
    public function trenBulanan(int $tahun): Collection
    {
        $bulan = match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%m', created_at) AS INTEGER)",
            'pgsql'  => 'EXTRACT(MONTH FROM created_at)',
            default  => 'MONTH(created_at)',
        };

        $perBulan = DiseaseReport::whereYear('created_at', $tahun)
            ->toBase()
            ->selectRaw("{$bulan} as bulan, COUNT(*) as jumlah")
            ->groupBy('bulan')
            ->pluck('jumlah', 'bulan');

        return collect(range(1, 12))->map(fn ($b) => (int) ($perBulan[$b] ?? 0));
    }

    public function daftarPenyakit(): Collection
    {
        return DiseaseReport::query()->distinct()->orderBy('nama_penyakit')->pluck('nama_penyakit');
    }
}
