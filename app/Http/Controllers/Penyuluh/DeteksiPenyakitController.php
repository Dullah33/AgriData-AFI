<?php

namespace App\Http\Controllers\Penyuluh;

use App\Http\Controllers\Concerns\MembutuhkanPenyuluh;
use App\Http\Controllers\Controller;
use App\Models\DiseaseReport;
use App\Models\ExtensionOfficer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeteksiPenyakitController extends Controller
{
    use MembutuhkanPenyuluh;

    // GET /penyuluh/deteksi-penyakit
    // "Deteksi Penyakit di Wilayah Binaan" (BAB 2A.6): hasil AI Scanner dari
    // petani di wilayah binaan, dengan opsi memberi rekomendasi tindak lanjut.
    public function index()
    {
        if (! $officer = $this->penyuluhAtauNull()) {
            return view('penyuluh.belum-terdaftar');
        }

        $laporan = DiseaseReport::with(['user', 'lahan'])
            ->diWilayah($officer->wilayah_binaan)
            ->latest()
            ->paginate(10);

        // Hitungan per status dalam satu query, bukan satu query per status
        $perStatus = DiseaseReport::diWilayah($officer->wilayah_binaan)
            ->whereIn('status', ['baru', 'ditindaklanjuti'])
            ->toBase()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total'           => $laporan->total(),
            'baru'            => (int) ($perStatus['baru'] ?? 0),
            'ditindaklanjuti' => (int) ($perStatus['ditindaklanjuti'] ?? 0),
        ];

        return view('penyuluh.deteksi-penyakit.index', compact('officer', 'laporan', 'stats'));
    }

    // GET /penyuluh/deteksi-penyakit/{laporan}
    public function show(DiseaseReport $laporan)
    {
        $officer = $this->pastikanDiWilayahBinaan($laporan);
        $this->tandaiDitinjau($laporan);

        return view('penyuluh.deteksi-penyakit.show', [
            'laporan' => $laporan->loadMissing(['user', 'lahan']),
            'officer' => $officer,
        ]);
    }

    // POST /penyuluh/deteksi-penyakit/{laporan}/tindak-lanjut
    public function tindakLanjut(Request $request, DiseaseReport $laporan)
    {
        $this->pastikanDiWilayahBinaan($laporan);

        $data = $request->validate([
            'rekomendasi_tindak_lanjut' => 'required|string|max:3000',
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

    private function pastikanDiWilayahBinaan(DiseaseReport $laporan): ExtensionOfficer
    {
        $officer = $this->penyuluh();

        abort_if($laporan->wilayah?->nama_wilayah !== $officer->wilayah_binaan, 403, 'Laporan ini berada di luar wilayah binaan Anda.');

        return $officer;
    }

    // Laporan baru otomatis berstatus "ditinjau" saat pertama dibuka penyuluh
    private function tandaiDitinjau(DiseaseReport $laporan): void
    {
        if ($laporan->status === 'baru') {
            $laporan->update(['status' => 'ditinjau', 'ditinjau_oleh' => Auth::id(), 'ditinjau_at' => now()]);
        }
    }
}
