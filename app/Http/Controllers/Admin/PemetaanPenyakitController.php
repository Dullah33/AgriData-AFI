<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiseaseReport;
use App\Models\Wilayah;
use Illuminate\Http\Request;

class PemetaanPenyakitController extends Controller
{
    // GET /admin/pemetaan-penyakit
    // "Dashboard Pemetaan Penyakit" (BAB 2.5.2 & 2A.2): peta lokasi
    // laporan penyakit, filter jenis penyakit/tanaman/periode, grafik
    // tren bulanan, dan peringatan dini wilayah rentan (>=5 laporan
    // dalam 30 hari terakhir dianggap rentan — ambang sederhana, bisa
    // disesuaikan nanti kalau ada kebijakan resmi dari Dinas Pertanian).
    private const AMBANG_RENTAN = 5;

    public function index(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);

        $query = DiseaseReport::with(['user', 'wilayah'])->whereYear('created_at', $tahun);

        if ($request->filled('nama_penyakit')) {
            $query->where('nama_penyakit', $request->nama_penyakit);
        }

        if ($request->filled('tingkat_risiko')) {
            $query->where('tingkat_risiko', $request->tingkat_risiko);
        }

        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }

        $laporans = (clone $query)->latest()->paginate(10)->withQueryString();

        // Titik peta: semua laporan tahun berjalan (sesuai filter) yang punya koordinat
        $titikPeta = (clone $query)->whereNotNull('latitude')->whereNotNull('longitude')
            ->get(['id', 'nama_penyakit', 'tingkat_risiko', 'latitude', 'longitude', 'wilayah_id'])
            ->map(fn ($d) => [
                'nama_penyakit'  => $d->nama_penyakit,
                'tingkat_risiko' => $d->tingkat_risiko,
                'latitude'       => $d->latitude,
                'longitude'      => $d->longitude,
            ]);

        // Grafik tren bulanan (BAB 2.5.2)
        $trenBulanan = DiseaseReport::whereYear('created_at', $tahun)
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->groupBy('bulan')
            ->pluck('jumlah', 'bulan');

        $trenChart = collect(range(1, 12))->map(fn ($b) => $trenBulanan->get($b, 0));

        // Peringatan dini: wilayah dengan laporan terbanyak dalam 30 hari terakhir
        $wilayahRentan = Wilayah::all()
            ->map(function (Wilayah $w) {
                $jumlah = DiseaseReport::where('wilayah_id', $w->id)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count();

                return ['wilayah' => $w->nama_wilayah, 'jumlah' => $jumlah];
            })
            ->filter(fn ($w) => $w['jumlah'] >= self::AMBANG_RENTAN)
            ->sortByDesc('jumlah')
            ->values();

        return view('admin.pemetaan-penyakit.index', [
            'laporans'         => $laporans,
            'titikPeta'        => $titikPeta,
            'trenChart'        => $trenChart,
            'wilayahRentan'    => $wilayahRentan,
            'wilayahList'      => Wilayah::all(),
            'daftarPenyakit'   => DiseaseReport::distinct()->pluck('nama_penyakit'),
            'tahun'            => (int) $tahun,
            'ambangRentan'     => self::AMBANG_RENTAN,
        ]);
    }
}
