<?php

namespace App\Http\Controllers\Petani;

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

    // GET /petani/deteksi-penyakit
    // Halaman "Riwayat Deteksi Penyakit" (BAB 2A.3): daftar riwayat laporan
    // deteksi penyakit yang pernah dibuat petani.
    public function index()
    {
        $laporan = DiseaseReport::where('user_id', Auth::id())
            ->latest()->paginate(9);

        return view('petani.deteksi-penyakit.index', compact('laporan'));
    }

    // GET /petani/deteksi-penyakit/scan
    // Halaman "AI Scanner Penyakit Tanaman" (BAB 2A.3): form upload foto.
    public function create()
    {
        $petaniProfile = Auth::user()->petaniProfile;
        $lahans = $petaniProfile ? $petaniProfile->lahans()->get() : collect();

        return view('petani.deteksi-penyakit.create', compact('lahans'));
    }

    // POST /petani/deteksi-penyakit/scan
    // Menerima foto dari kamera (WebRTC) atau galeri, menganalisisnya lewat
    // AI Scanner, lalu menyimpan foto + hasilnya sebagai DiseaseReport.
    public function store(ScanPenyakitRequest $request)
    {
        $data = $request->validated();
        $petaniProfile = Auth::user()->petaniProfile;

        // Pastikan lahan yang dipilih benar-benar milik petani ini
        $lahan = ! empty($data['lahan_id']) ? $petaniProfile?->lahans()->find($data['lahan_id']) : null;

        $foto = $request->file('foto_tanaman');
        $hasil = $this->analisisFoto($foto, $lahan?->tanaman_aktif);

        // Tentukan koordinat laporan: prioritas dari titik tengah poligon
        // lahan yang dipilih, jatuh ke koordinat wilayah kalau lahan tidak
        // dipilih/tidak punya poligon. Sebelumnya kolom ini TIDAK PERNAH
        // diisi sama sekali, sehingga laporan tidak pernah muncul di peta
        // manapun (Pemetaan Penyakit maupun Peta Lahan & Wilayah).
        $koordinat = $lahan?->centroid();

        if (! $koordinat && $petaniProfile?->wilayah) {
            $koordinat = [$petaniProfile->wilayah->latitude, $petaniProfile->wilayah->longitude];
        }

        $laporan = $this->simpanLaporan($foto, $this->atributHasil($hasil) + [
            'user_id'    => Auth::id(),
            'lahan_id'   => $lahan?->id,
            'wilayah_id' => $petaniProfile?->wilayah_id,
            'latitude'   => $koordinat[0] ?? null,
            'longitude'  => $koordinat[1] ?? null,
        ]);

        return $this->arahkanKeHasil($request, route('petani.deteksi-penyakit.show', $laporan));
    }

    // GET /petani/deteksi-penyakit/{laporan}
    public function show(DiseaseReport $laporan)
    {
        abort_if($laporan->user_id !== Auth::id(), 403);

        return view('petani.deteksi-penyakit.show', compact('laporan'));
    }
}
