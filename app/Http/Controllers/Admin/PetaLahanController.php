<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiseaseReport;
use App\Models\Lahan;
use App\Models\Wilayah;
use Illuminate\Http\Request;

class PetaLahanController extends Controller
{
    // "Peta Lahan, Wilayah & Penyakit" — gabungan dari fitur yang tadinya
    // terpisah (Peta Lahan & Wilayah + Pemetaan Penyakit), supaya dalam
    // satu peta admin langsung bisa lihat lahan ini milik siapa DAN
    // sedang ada laporan penyakit apa di situ, tanpa pindah halaman.
    private const AMBANG_RENTAN = 5;

    public function index(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);

        $wilayahList = Wilayah::withCount('petaniProfiles')->get();

        $markerWilayah = $wilayahList->map(fn (Wilayah $w) => [
            'nama_wilayah'  => $w->nama_wilayah,
            'latitude'      => $w->latitude,
            'longitude'     => $w->longitude,
            'jumlah_petani' => $w->petani_profiles_count,
        ])->filter(fn ($w) => $w['latitude'] && $w['longitude'])->values();

        // Query dasar laporan penyakit, kena filter jenis penyakit/tingkat
        // risiko/tahun dari form filter.
        $laporanQuery = DiseaseReport::with(['user', 'wilayah'])->whereYear('created_at', $tahun);

        if ($request->filled('nama_penyakit')) {
            $laporanQuery->where('nama_penyakit', $request->nama_penyakit);
        }
        if ($request->filled('tingkat_risiko')) {
            $laporanQuery->where('tingkat_risiko', $request->tingkat_risiko);
        }
        if ($request->filled('wilayah_id')) {
            $laporanQuery->where('wilayah_id', $request->wilayah_id);
        }

        // Poligon lahan, masing-masing dilengkapi laporan penyakit yang
        // terkait dengannya (lewat disease_reports.lahan_id) supaya popup
        // di peta bisa langsung nunjukin "lahan ini milik siapa + lagi
        // kena penyakit apa".
        $lahans = Lahan::with(['petaniProfile.user', 'diseaseReports' => function ($q) use ($tahun, $request) {
                $q->whereYear('created_at', $tahun);
                if ($request->filled('nama_penyakit')) {
                    $q->where('nama_penyakit', $request->nama_penyakit);
                }
                if ($request->filled('tingkat_risiko')) {
                    $q->where('tingkat_risiko', $request->tingkat_risiko);
                }
            }])
            ->whereNotNull('koordinat_poligon')
            ->get()
            ->filter(fn (Lahan $l) => is_array($l->koordinat_poligon) && count($l->koordinat_poligon) >= 3)
            ->map(function (Lahan $l) {
                $urutanRisiko = ['tinggi' => 3, 'sedang' => 2, 'rendah' => 1];
                $risikoTertinggi = $l->diseaseReports
                    ->sortByDesc(fn ($d) => $urutanRisiko[$d->tingkat_risiko] ?? 0)
                    ->first()?->tingkat_risiko;

                return [
                    'nama_lahan'       => $l->nama_lahan,
                    'petani'           => $l->petaniProfile->user->username ?? '-',
                    'tanaman_aktif'    => $l->tanaman_aktif,
                    'luas_ha'          => $l->luas_ha,
                    'status'           => $l->status,
                    'koordinat'        => $l->koordinat_poligon,
                    'risiko_tertinggi' => $risikoTertinggi,
                    'laporan_penyakit' => $l->diseaseReports->map(fn ($d) => [
                        'nama_penyakit'  => $d->nama_penyakit,
                        'tingkat_risiko' => $d->tingkat_risiko,
                        'tanggal'        => $d->created_at->format('d M Y'),
                    ])->values(),
                ];
            })->values();

        // Laporan yang TIDAK terkait lahan manapun (mis. dibuat tanpa
        // pilih lahan, fallback ke titik tengah wilayah) — ditampilkan
        // sebagai marker titik terpisah, bukan di dalam poligon lahan.
        $titikPenyakitTanpaLahan = (clone $laporanQuery)->whereNull('lahan_id')
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->get()
            ->map(fn ($d) => [
                'nama_penyakit'  => $d->nama_penyakit,
                'tingkat_risiko' => $d->tingkat_risiko,
                'latitude'       => $d->latitude,
                'longitude'      => $d->longitude,
            ])->values();

        // Titik untuk layer heatmap: SEMUA laporan yang punya koordinat
        // (baik yang terkait lahan maupun tidak), bobot berdasar risiko.
        $bobotRisiko = ['tinggi' => 1.0, 'sedang' => 0.6, 'rendah' => 0.3];
        $heatmapPoints = (clone $laporanQuery)->whereNotNull('latitude')->whereNotNull('longitude')
            ->get()
            ->map(fn ($d) => [$d->latitude, $d->longitude, $bobotRisiko[$d->tingkat_risiko] ?? 0.3])
            ->values();

        $laporans = (clone $laporanQuery)->with('lahan')->latest()->paginate(10)->withQueryString();

        // Grafik tren bulanan
        $trenBulanan = DiseaseReport::whereYear('created_at', $tahun)
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->groupBy('bulan')->pluck('jumlah', 'bulan');
        $trenChart = collect(range(1, 12))->map(fn ($b) => $trenBulanan->get($b, 0));

        // Peringatan dini: wilayah dengan laporan terbanyak 30 hari terakhir
        $wilayahRentan = $wilayahList
            ->map(function (Wilayah $w) {
                $jumlah = DiseaseReport::where('wilayah_id', $w->id)
                    ->where('created_at', '>=', now()->subDays(30))->count();

                return ['wilayah' => $w->nama_wilayah, 'jumlah' => $jumlah];
            })
            ->filter(fn ($w) => $w['jumlah'] >= self::AMBANG_RENTAN)
            ->sortByDesc('jumlah')->values();

        return view('admin.peta-lahan.index', [
            'wilayahList'             => $wilayahList,
            'markerWilayah'           => $markerWilayah,
            'poligonLahan'            => $lahans,
            'titikPenyakitTanpaLahan' => $titikPenyakitTanpaLahan,
            'heatmapPoints'           => $heatmapPoints,
            'laporans'                => $laporans,
            'trenChart'               => $trenChart,
            'wilayahRentan'           => $wilayahRentan,
            'daftarPenyakit'          => DiseaseReport::distinct()->pluck('nama_penyakit'),
            'tahun'                   => (int) $tahun,
            'ambangRentan'            => self::AMBANG_RENTAN,
        ]);
    }
}
