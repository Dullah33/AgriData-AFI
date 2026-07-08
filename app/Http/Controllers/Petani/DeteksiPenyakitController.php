<?php

namespace App\Http\Controllers\Petani;

use App\Exceptions\PlantNotDetectedException;
use App\Http\Controllers\Controller;
use App\Models\DiseaseReport;
use App\Services\DiseaseDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DeteksiPenyakitController extends Controller
{
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
    public function store(Request $request)
    {
        $data = $request->validate([
            'foto_tanaman' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'lahan_id'     => 'nullable|exists:lahan,id',
        ], [
            'foto_tanaman.required' => 'Foto tanaman harus diupload.',
            'foto_tanaman.image'    => 'File harus berupa gambar.',
            'foto_tanaman.mimes'    => 'Format foto harus JPG atau PNG.',
            'foto_tanaman.max'      => 'Ukuran foto maksimal 5MB.',
        ]);

        $petaniProfile = Auth::user()->petaniProfile;

        // Pastikan lahan yang dipilih benar-benar milik petani ini
        if (! empty($data['lahan_id']) && $petaniProfile) {
            $milikSendiri = $petaniProfile->lahans()->where('id', $data['lahan_id'])->exists();
            if (! $milikSendiri) {
                $data['lahan_id'] = null;
            }
        }

        $path = $request->file('foto_tanaman')->store('deteksi-penyakit', 'public');

        try {
            $hasil = $this->aiScanner->analisis(Storage::disk('public')->path($path));
        } catch (PlantNotDetectedException $e) {
            Storage::disk('public')->delete($path);

            return back()->withInput()->withErrors(['foto_tanaman' => $e->getMessage()]);
        }

        // Tentukan koordinat laporan: prioritas dari titik tengah poligon
        // lahan yang dipilih, jatuh ke koordinat wilayah kalau lahan tidak
        // dipilih/tidak punya poligon. Sebelumnya kolom ini TIDAK PERNAH
        // diisi sama sekali, sehingga laporan tidak pernah muncul di peta
        // manapun (Pemetaan Penyakit maupun Peta Lahan & Wilayah).
        $lahan = ! empty($data['lahan_id']) ? $petaniProfile?->lahans()->find($data['lahan_id']) : null;
        $koordinat = $lahan?->centroid();

        if (! $koordinat && $petaniProfile?->wilayah) {
            $koordinat = [$petaniProfile->wilayah->latitude, $petaniProfile->wilayah->longitude];
        }

        $laporan = DiseaseReport::create([
            'user_id'          => Auth::id(),
            'lahan_id'         => $data['lahan_id'] ?? null,
            'wilayah_id'       => $petaniProfile?->wilayah_id,
            'foto_tanaman'     => $path,
            'nama_penyakit'    => $hasil['nama_penyakit'],
            'confidence_score' => $hasil['confidence_score'],
            'gejala'           => $hasil['gejala'],
            'penyebab'         => $hasil['penyebab'],
            'penanganan'       => $hasil['penanganan'],
            'tingkat_risiko'   => $hasil['tingkat_risiko'],
            'latitude'         => $koordinat[0] ?? null,
            'longitude'        => $koordinat[1] ?? null,
            'status'           => 'baru',
        ]);

        return redirect()->route('petani.deteksi-penyakit.show', $laporan)
            ->with('success', 'Analisis selesai! Berikut hasil deteksinya.');
    }

    // GET /petani/deteksi-penyakit/{laporan}
    public function show(DiseaseReport $laporan)
    {
        abort_if($laporan->user_id !== Auth::id(), 403);

        return view('petani.deteksi-penyakit.show', compact('laporan'));
    }
}
