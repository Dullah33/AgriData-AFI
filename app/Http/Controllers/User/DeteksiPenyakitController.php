<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Concerns\MenanganiScanPenyakit;
use App\Http\Controllers\Controller;
use App\Http\Requests\ScanPenyakitRequest;
use App\Models\DiseaseReport;
use App\Services\DiseaseDetectionService;
use Illuminate\Support\Facades\Auth;

class DeteksiPenyakitController extends Controller
{
    use MenanganiScanPenyakit;

    public function __construct(private DiseaseDetectionService $aiScanner)
    {
    }

    // GET /ai-scanner
    // "AI Scanner Penyakit Tanaman (Terbatas)" (BAB 2A.4) — versi edukatif
    // untuk masyarakat umum: tidak terhubung ke lahan/wilayah binaan
    // Penyuluh, hanya menampilkan hasil analisis + info edukasi.
    public function create()
    {
        return view('user.deteksi-penyakit.create');
    }

    // POST /ai-scanner
    // Menerima foto dari kamera (WebRTC) atau galeri, menganalisisnya lewat
    // AI Scanner, lalu menyimpan foto + hasilnya sebagai DiseaseReport.
    public function store(ScanPenyakitRequest $request)
    {
        $foto = $request->file('foto_tanaman');
        $hasil = $this->analisisFoto($foto);

        $laporan = $this->simpanLaporan($foto, $this->atributHasil($hasil) + [
            'user_id' => Auth::id(),
        ]);

        return $this->arahkanKeHasil($request, route('user.deteksi-penyakit.show', $laporan));
    }

    // GET /ai-scanner/{laporan}
    public function show(DiseaseReport $laporan)
    {
        abort_if($laporan->user_id !== Auth::id(), 403);

        return view('user.deteksi-penyakit.show', compact('laporan'));
    }
}
