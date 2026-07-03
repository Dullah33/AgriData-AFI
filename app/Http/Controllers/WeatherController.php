<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Services\LocationService;
use App\Services\WeatherService;
use App\Services\AnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    protected $locationService;
    protected $weatherService;
    protected $analysisService;

    public function __construct(
        LocationService $locationService,
        WeatherService $weatherService,
        AnalysisService $analysisService
    ) {
        $this->locationService = $locationService;
        $this->weatherService = $weatherService;
        $this->analysisService = $analysisService;
    }

    // Tampilkan halaman analisis cuaca
    public function index()
    {
        return view('weather.index');
    }

    //API: Get daftar Provinsi (dari EMSIFA API)
    public function getProvinces()
    {
        try {
            $response = Http::timeout(10)->get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Gagal memuat data provinsi'], 500);
            }
            
            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error("Get provinces error: " . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    //API: Get daftar Kabupaten berdasarkan kode Provinsi
    public function getRegencies($provCode)
    {
        try {
            $url = "https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$provCode}.json";
            $response = Http::timeout(10)->get($url);
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Gagal memuat data kabupaten'], 500);
            }
            
            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error("Get regencies error: " . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    //API: Get daftar Kecamatan berdasarkan kode Kabupaten
    public function getDistricts($regCode)
    {
        try {
            $url = "https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$regCode}.json";
            $response = Http::timeout(10)->get($url);
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Gagal memuat data kecamatan'], 500);
            }
            
            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error("Get districts error: " . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function getRecommendations($provinsi, $kabupaten, $kecamatan)
    {
        $provinsi = urldecode($provinsi);
        $kabupaten = urldecode($kabupaten);
        $kecamatan = urldecode($kecamatan);

        try {
            // 1. Dapatkan koordinat dari nama wilayah
            $coords = $this->locationService->geocode($provinsi, $kabupaten, $kecamatan);
            
            // 2. Dapatkan data cuaca real-time
            $weather = $this->weatherService->getWeather($coords['latitude'], $coords['longitude']);
            
            // 3. Ambil semua tanaman dari database
            $plants = Plant::all();
            
            // 4. Hitung matching score untuk setiap tanaman
            $recommendations = $plants->map(function($plant) use ($weather) {
                $analysisResult = $this->analysisService->calculateMatchingScore($plant, $weather);
                $scoreValue = $analysisResult['score'];
                $status = $this->analysisService->determineStatus($scoreValue);
                
                return [
                    'id' => $plant->id,
                    'kode' => $plant->kode,
                    'nama' => $plant->nama,
                    'gambar' => $plant->gambar,
                    'score' => $scoreValue,
                    'status' => $status,
                    'current_conditions' => [
                        'suhu' => $weather['suhu'],
                        'kelembaban' => $weather['kelembaban'],
                        'curah_hujan' => $weather['curah_hujan']
                    ],
                    'match_details' => [
                        'suhu_match' => $analysisResult['details']['suhu']['match'],
                        'kelembaban_match' => $analysisResult['details']['kelembaban']['match'],
                        'musim_match' => stripos($plant->musim ?? '', 'hujan') !== false
                    ]
                ];
            })
            ->sortByDesc('score')
            ->take(5)
            ->values();
            
            return response()->json([
                'success' => true,
                'location' => [
                    'provinsi' => $provinsi,
                    'kabupaten' => $kabupaten,
                    'kecamatan' => $kecamatan,
                    'latitude' => $coords['latitude'],
                    'longitude' => $coords['longitude']
                ],
                'weather' => $weather,
                'recommendations' => $recommendations
            ]);
            
        } catch (\Exception $e) {
            Log::error("Recommendations error: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    //API: Analisis detail untuk tanaman spesifik
    public function analyze(Request $request)
    {
        $validated = $request->validate([
            'provinsi' => 'required|string',
            'kabupaten' => 'required|string',
            'kecamatan' => 'required|string',
            'plant_id' => 'required|exists:plants,id'
        ]);
        
        try {
            // 1. Dapatkan koordinat
            $coords = $this->locationService->geocode(
                $validated['provinsi'],
                $validated['kabupaten'],
                $validated['kecamatan']
            );
            
            // 2. Dapatkan data cuaca
            $weatherData = $this->weatherService->getWeather($coords['latitude'], $coords['longitude']);
            
            // 3. Ambil data tanaman
            $plant = Plant::findOrFail($validated['plant_id']);
            
            // 4. Lakukan analisis
            $analysisResult = $this->analysisService->calculateMatchingScore($plant, $weatherData);
            $scoreValue = $analysisResult['score'];
            $status = $this->analysisService->determineStatus($scoreValue);
            $recommendation = $this->analysisService->generateRecommendation($plant, $weatherData);

            return response()->json([
                'success' => true,
                'plant' => $plant,
                'location' => $coords,
                'weather' => $weatherData,
                'analysis' => [
                    'score' => $scoreValue, // ← sekarang integer
                    'status' => $status,
                    'recommendation' => $recommendation,
                    'match_details' => $analysisResult['details'] // ← kirim details ke frontend
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //API: Get detail informasi tanaman
    public function getPlantDetail($id)
    {
        try {
            $plant = Plant::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $plant
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Tanaman tidak ditemukan'], 404);
        }
    }

    //API: Get semua tanaman untuk dropdown
    public function getPlants()
    {
        try {
            $plants = Plant::select('id', 'kode', 'nama', 'gambar')->get();
            
            return response()->json([
                'success' => true,
                'data' => $plants
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Gagal memuat data tanaman'], 500);
        }
    }
}