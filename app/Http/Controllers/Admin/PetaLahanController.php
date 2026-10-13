<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PetaPenyakitService;
use Illuminate\Http\Request;

class PetaLahanController extends Controller
{
    // GET /admin/peta-lahan
    // "Peta Lahan, Wilayah & Penyakit": dalam satu peta admin melihat lahan
    // milik siapa sekaligus laporan penyakit di lahan/wilayah tersebut.
    public function index(Request $request, PetaPenyakitService $peta)
    {
        $filter = $peta->filterDari($request);
        $wilayah = $peta->wilayahDenganStatistik();

        return view('admin.peta-lahan.index', [
            'wilayahList'             => $wilayah,
            'markerWilayah'           => $peta->markerWilayah($wilayah),
            'poligonLahan'            => $peta->poligonLahan($filter),
            'titikPenyakitTanpaLahan' => $peta->titikTanpaLahan($filter),
            'heatmapPoints'           => $peta->titikHeatmap($filter),
            'laporans'                => $peta->laporan($filter)->with(['user', 'wilayah', 'lahan'])->latest()->paginate(10)->withQueryString(),
            'trenChart'               => $peta->trenBulanan($filter['tahun']),
            'wilayahRentan'           => $peta->wilayahRentan($wilayah),
            'daftarPenyakit'          => $peta->daftarPenyakit(),
            'tahun'                   => $filter['tahun'],
            'ambangRentan'            => PetaPenyakitService::AMBANG_RENTAN,
        ]);
    }
}
