<?php

namespace App\Http\Controllers\User;

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

    // GET /ai-scanner
    // "AI Scanner Penyakit Tanaman (Terbatas)" (BAB 2A.4) — versi edukatif
    // untuk masyarakat umum: tidak terhubung ke lahan/wilayah binaan
    // Penyuluh, hanya menampilkan hasil analisis + info edukasi.
    public function create()
    {
        return view('user.deteksi-penyakit.create');
    }

    // POST /ai-scanner
    public function store(Request $request)
    {
        $request->validate([
            'foto_tanaman' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'foto_tanaman.required' => 'Foto tanaman harus diupload.',
            'foto_tanaman.image'    => 'File harus berupa gambar.',
            'foto_tanaman.mimes'    => 'Format foto harus JPG atau PNG.',
            'foto_tanaman.max'      => 'Ukuran foto maksimal 5MB.',
        ]);

        $path = $request->file('foto_tanaman')->store('deteksi-penyakit', 'public');

        $hasil = $this->aiScanner->analisis(Storage::disk('public')->path($path));

        $laporan = DiseaseReport::create([
            'user_id'          => Auth::id(),
            'foto_tanaman'     => $path,
            'nama_penyakit'    => $hasil['nama_penyakit'],
            'confidence_score' => $hasil['confidence_score'],
            'gejala'           => $hasil['gejala'],
            'penyebab'         => $hasil['penyebab'],
            'penanganan'       => $hasil['penanganan'],
            'tingkat_risiko'   => $hasil['tingkat_risiko'],
            'status'           => 'baru',
        ]);

        return redirect()->route('user.deteksi-penyakit.show', $laporan)
            ->with('success', 'Analisis selesai! Berikut hasil deteksinya.');
    }

    // GET /ai-scanner/{laporan}
    public function show(DiseaseReport $laporan)
    {
        abort_if($laporan->user_id !== Auth::id(), 403);

        return view('user.deteksi-penyakit.show', compact('laporan'));
    }
}
