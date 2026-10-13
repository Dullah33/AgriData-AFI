<?php

namespace App\Http\Controllers\Concerns;

use App\Exceptions\AiScannerUnavailableException;
use App\Exceptions\PlantNotDetectedException;
use App\Models\DiseaseReport;
use App\Services\DiseaseDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Alur bersama AI Scanner untuk Petani\DeteksiPenyakitController dan
 * User\DeteksiPenyakitController: analisis foto -> simpan foto ke storage
 * public -> simpan DiseaseReport -> respons (JSON untuk request AJAX dari
 * halaman scanner, redirect biasa untuk form tanpa JavaScript).
 *
 * Controller yang memakai trait ini wajib punya properti $aiScanner.
 *
 * @property DiseaseDetectionService $aiScanner
 */
trait MenanganiScanPenyakit
{
    /**
     * Foto dianalisis langsung dari file sementara hasil upload. Foto baru
     * disimpan permanen kalau analisis berhasil, jadi tidak ada file yatim
     * di storage saat foto ditolak atau layanan AI sedang gangguan.
     *
     * @throws ValidationException
     */
    protected function analisisFoto(UploadedFile $foto, ?string $namaTanaman = null): array
    {
        try {
            return $this->aiScanner->analisis($foto->getRealPath(), $namaTanaman);
        } catch (PlantNotDetectedException|AiScannerUnavailableException $e) {
            // Ditampilkan sebagai error di field foto: untuk request AJAX
            // otomatis jadi respons 422 JSON, untuk form biasa jadi redirect
            // balik dengan pesan error.
            throw ValidationException::withMessages(['foto_tanaman' => $e->getMessage()]);
        }
    }

    protected function simpanLaporan(UploadedFile $foto, array $atribut): DiseaseReport
    {
        $path = $foto->store('deteksi-penyakit', 'public');

        if (! $path) {
            throw new \RuntimeException('Foto tidak bisa disimpan ke storage public. Periksa permission folder storage/app/public.');
        }

        try {
            return DiseaseReport::create(['foto_tanaman' => $path, 'status' => 'baru'] + $atribut);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);

            throw $e;
        }
    }

    // Field hasil analisis AI yang disimpan ke DiseaseReport
    protected function atributHasil(array $hasil): array
    {
        return [
            'nama_penyakit'    => $hasil['nama_penyakit'],
            'confidence_score' => $hasil['confidence_score'],
            'gejala'           => $hasil['gejala'],
            'penyebab'         => $hasil['penyebab'],
            'penanganan'       => $hasil['penanganan'],
            'tingkat_risiko'   => $hasil['tingkat_risiko'],
            'hasil_ai'         => $hasil['hasil_ai'] ?? null,
        ];
    }

    protected function arahkanKeHasil(Request $request, string $url): JsonResponse|RedirectResponse
    {
        $pesan = 'Analisis selesai! Berikut hasil deteksinya.';

        if ($request->expectsJson()) {
            $request->session()->flash('success', $pesan);

            return response()->json(['message' => $pesan, 'redirect' => $url], 201);
        }

        return redirect($url)->with('success', $pesan);
    }
}
