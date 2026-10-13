<?php

namespace App\Http\Controllers;

use App\Exceptions\CuacaTidakTersediaException;
use App\Exceptions\LokasiTidakDitemukanException;
use App\Models\Plant;
use App\Services\RekomendasiTanamService;
use App\Services\WilayahIndonesiaService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class WeatherController extends Controller
{
    public function __construct(private RekomendasiTanamService $rekomendasi) {}

    // GET /cuaca
    public function index()
    {
        return view('weather.index');
    }

    // GET /api/provinces, /api/regencies/{kode}, /api/districts/{kode}
    // Proxy ke API wilayah EMSIFA (di-cache, lihat WilayahIndonesiaService)
    public function getProvinces(WilayahIndonesiaService $wilayah): JsonResponse
    {
        return $this->amankan(fn () => response()->json($wilayah->provinsi()), 'Gagal memuat data provinsi');
    }

    public function getRegencies(WilayahIndonesiaService $wilayah, string $provCode): JsonResponse
    {
        return $this->amankan(fn () => response()->json($wilayah->kabupaten($provCode)), 'Gagal memuat data kabupaten');
    }

    public function getDistricts(WilayahIndonesiaService $wilayah, string $regCode): JsonResponse
    {
        return $this->amankan(fn () => response()->json($wilayah->kecamatan($regCode)), 'Gagal memuat data kecamatan');
    }

    // GET /api/cuaca/recommendations/{provinsi}/{kabupaten}/{kecamatan}
    // Lima tanaman paling cocok dengan cuaca saat ini di kecamatan tersebut
    public function getRecommendations(string $provinsi, string $kabupaten, string $kecamatan): JsonResponse
    {
        return $this->amankan(function () use ($provinsi, $kabupaten, $kecamatan) {
            $kondisi = $this->rekomendasi->kondisi(urldecode($provinsi), urldecode($kabupaten), urldecode($kecamatan));

            return response()->json([
                'success'         => true,
                'location'        => $kondisi['lokasi'],
                'weather'         => $kondisi['cuaca'],
                'recommendations' => $this->rekomendasi->teratas($kondisi['cuaca']),
            ]);
        });
    }

    // POST /api/cuaca/analyze
    // Analisis detail satu tanaman terhadap cuaca di lokasi yang dipilih
    public function analyze(Request $request): JsonResponse
    {
        $data = $request->validate([
            'provinsi'  => 'required|string|max:255',
            'kabupaten' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'plant_id'  => 'required|exists:plants,id',
        ]);

        return $this->amankan(function () use ($data) {
            $kondisi = $this->rekomendasi->kondisi($data['provinsi'], $data['kabupaten'], $data['kecamatan']);
            $plant = Plant::findOrFail($data['plant_id']);

            return response()->json([
                'success'  => true,
                'plant'    => $plant,
                'location' => $kondisi['lokasi'],
                'weather'  => $kondisi['cuaca'],
                'analysis' => $this->rekomendasi->analisis($plant, $kondisi['cuaca']),
            ]);
        });
    }

    // GET /api/tanaman/{plant}/detail
    public function getPlantDetail(Plant $plant): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $plant]);
    }

    // GET /api/tanaman (untuk dropdown)
    public function getPlants(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => Plant::select('id', 'kode', 'nama', 'gambar')->orderBy('nama')->get(),
        ]);
    }

    /**
     * Error yang dipahami pengguna (lokasi/cuaca) dikirim apa adanya. Error
     * lain hanya dicatat di log; sebelumnya pesan exception mentah (bisa
     * berisi detail SQL atau URL internal) ikut dikirim ke browser.
     */
    private function amankan(Closure $aksi, string $pesanUmum = 'Terjadi kesalahan pada server. Silakan coba lagi.'): JsonResponse
    {
        try {
            return $aksi();
        } catch (LokasiTidakDitemukanException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (CuacaTidakTersediaException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 503);
        } catch (Throwable $e) {
            Log::error('Halaman cuaca: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['success' => false, 'error' => $pesanUmum], 500);
        }
    }
}
