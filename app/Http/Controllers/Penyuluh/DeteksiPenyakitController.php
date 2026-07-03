<?php

namespace App\Http\Controllers\Penyuluh;

use App\Http\Controllers\Controller;
use App\Models\DiseaseReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeteksiPenyakitController extends Controller
{
    // GET /penyuluh/deteksi-penyakit
    // "Deteksi Penyakit di Wilayah Binaan" (BAB 2A.6): daftar hasil AI
    // Scanner dari petani di wilayah binaan penyuluh, dengan opsi memberi
    // rekomendasi tindak lanjut (BAB 2.7 & KF-23).
    public function index()
    {
        $officer = Auth::user()->extensionOfficer;

        if (! $officer) {
            return view('penyuluh.belum-terdaftar');
        }

        $laporan = DiseaseReport::with(['user', 'lahan'])
            ->diWilayah($officer->wilayah_binaan)
            ->latest()
            ->paginate(10);

        $stats = [
            'total'            => $laporan->total(),
            'baru'             => DiseaseReport::diWilayah($officer->wilayah_binaan)->baru()->count(),
            'ditindaklanjuti'  => DiseaseReport::diWilayah($officer->wilayah_binaan)->ditindaklanjuti()->count(),
        ];

        return view('penyuluh.deteksi-penyakit.index', compact('officer', 'laporan', 'stats'));
    }

    // GET /penyuluh/deteksi-penyakit/{laporan}
    public function show(DiseaseReport $laporan)
    {
        $officer = $this->pastikanDiWilayahBinaan($laporan);

        return view('penyuluh.deteksi-penyakit.show', compact('laporan', 'officer'));
    }

    // POST /penyuluh/deteksi-penyakit/{laporan}/tindak-lanjut
    public function tindakLanjut(Request $request, DiseaseReport $laporan)
    {
        $this->pastikanDiWilayahBinaan($laporan);

        $data = $request->validate([
            'rekomendasi_tindak_lanjut' => 'required|string',
        ], [
            'rekomendasi_tindak_lanjut.required' => 'Rekomendasi tindak lanjut harus diisi.',
        ]);

        $laporan->update([
            'rekomendasi_tindak_lanjut' => $data['rekomendasi_tindak_lanjut'],
            'status'                    => 'ditindaklanjuti',
            'ditinjau_oleh'             => Auth::id(),
            'ditinjau_at'               => now(),
        ]);

        return redirect()->route('penyuluh.deteksi-penyakit.show', $laporan)
            ->with('success', 'Rekomendasi tindak lanjut berhasil dikirim.');
    }

    // Pastikan laporan yang diakses berada di wilayah binaan penyuluh yang
    // sedang login, sekaligus tandai statusnya "ditinjau" saat pertama dibuka.
    private function pastikanDiWilayahBinaan(DiseaseReport $laporan)
    {
        $officer = Auth::user()->extensionOfficer;
        abort_if(! $officer, 403, 'Profil penyuluh belum terdaftar.');

        $namaWilayahLaporan = $laporan->wilayah?->nama_wilayah;
        abort_if($namaWilayahLaporan !== $officer->wilayah_binaan, 403, 'Laporan ini berada di luar wilayah binaan Anda.');

        if ($laporan->status === 'baru') {
            $laporan->update(['status' => 'ditinjau', 'ditinjau_oleh' => Auth::id(), 'ditinjau_at' => now()]);
        }

        return $officer;
    }
}
