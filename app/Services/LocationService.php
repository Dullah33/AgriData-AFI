<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LocationService
{
    /**
     * Cache key prefix untuk geocoding
     */
    private const CACHE_PREFIX = 'geocode_';
    
    /**
     * Durasi cache: 30 hari (koordinat wilayah tidak sering berubah)
     */
    private const CACHE_DURATION = 2592000; 
    
    /**
     * Path file fallback koordinat Indonesia
     */
    private const FALLBACK_FILE = 'data/indonesia_coordinates.json';

    public function geocode(string $provinsi, string $kabupaten, string $kecamatan): array
    {
        $cacheKey = self::CACHE_PREFIX . md5("{$provinsi}|{$kabupaten}|{$kecamatan}");
        
        if (Cache::has($cacheKey)) {
            Log::info("Geocoding cache hit: {$cacheKey}");
            $cached = Cache::get($cacheKey);
            
            if (isset($cached['latitude']) && isset($cached['longitude'])) {
                return $cached;
            }
        }
        
        try {
            $result = $this->fetchFromNominatim($provinsi, $kabupaten, $kecamatan);
            
            //memastikan ada latitude dan longitude
            $normalizedResult = $this->normalizeGeocodeResult($result);
            
            Cache::put($cacheKey, $normalizedResult, self::CACHE_DURATION);
            Log::info("Geocoding cached: {$cacheKey}");
            
            return $normalizedResult;
            
        } catch (\Exception $e) {
            Log::warning("Nominatim API failed: {$e->getMessage()}");
            
            $fallback = $this->fetchFromFallback($provinsi, $kabupaten, $kecamatan);
            
            if ($fallback) {
                $normalizedFallback = $this->normalizeGeocodeResult($fallback);
                
                Log::info("Geocoding fallback success: {$cacheKey}");
                Cache::put($cacheKey, $normalizedFallback, self::CACHE_DURATION);
                
                return $normalizedFallback;
            }
            
            throw new \Exception(
                "Gagal mendapatkan koordinat untuk {$kecamatan}, {$kabupaten}, {$provinsi}. " .
                "Silakan coba lagi nanti atau periksa nama wilayah."
            );
        }
    }

    private function normalizeGeocodeResult(array $result): array
    {
        $normalized = [
            'latitude' => null,
            'longitude' => null,
            'provinsi' => $result['provinsi'] ?? null,
            'kabupaten' => $result['kabupaten'] ?? null,
            'kecamatan' => $result['kecamatan'] ?? null,
        ];
        
        // Cek berbagai kemungkinan key untuk latitude
        if (isset($result['latitude'])) {
            $normalized['latitude'] = $result['latitude'];
        } elseif (isset($result['lat'])) {
            $normalized['latitude'] = $result['lat'];
        } elseif (isset($result['Latitude'])) {
            $normalized['latitude'] = $result['Latitude'];
        }
        
        // Cek berbagai kemungkinan key untuk longitude
        if (isset($result['longitude'])) {
            $normalized['longitude'] = $result['longitude'];
        } elseif (isset($result['lon'])) {
            $normalized['longitude'] = $result['lon'];
        } elseif (isset($result['Longitude'])) {
            $normalized['longitude'] = $result['Longitude'];
        } elseif (isset($result['long'])) {
            $normalized['longitude'] = $result['long'];
        }
        
        return $normalized;
    }
    
    /**
     * Fetch dari Nominatim API dengan rate limit awareness
     */
    private function fetchFromNominatim(string $prov, string $kab, string $kec): array
    {
        
        usleep(100000); 
        
        $query = urlencode("{$kec}, {$kab}, {$prov}, Indonesia");
        $url = "https://nominatim.openstreetmap.org/search?format=json&q={$query}&limit=1";
        
        $response = Http::withHeaders([
            'User-Agent' => 'AgriData-WeatherSystem/1.0 (Contact: ferry_fifyli8@student.uns.ac.id)'
        ])
        ->timeout(10)
        ->get($url);
        
        $data = $response->json();
        
        if (!$response->successful() || empty($data)) {
            $queryFallback = urlencode("{$kab}, {$prov}, Indonesia");
            $urlFallback = "https://nominatim.openstreetmap.org/search?format=json&q={$queryFallback}&limit=1";
            
            $response = Http::withHeaders([
                'User-Agent' => 'AgriData-WeatherSystem/1.0 (Contact: ferry_fifyli8@student.uns.ac.id)'
            ])
            ->timeout(10)
            ->get($urlFallback);
            
            $data = $response->json();
            
            if (!$response->successful() || empty($data)) {
                throw new \Exception("Nominatim API returned no results");
            }
        }
        
        return [
            'latitude'  => (float) $data[0]['lat'],
            'longitude' => (float) $data[0]['lon'],
            'provinsi' => $prov,
            'kabupaten' => $kab,
            'kecamatan' => $kec,
            'display_name' => $data[0]['display_name'],
            'source' => 'nominatim' // Untuk debugging
        ];
    }
    
    /**
     * Fetch dari file JSON lokal sebagai fallback
     */
    private function fetchFromFallback(string $prov, string $kab, string $kec): ?array
    {
        $filePath = resource_path(self::FALLBACK_FILE);
        
        if (!file_exists($filePath)) {
            Log::warning("Fallback file not found: {$filePath}");
            return null;
        }
        
        try {
            $json = file_get_contents($filePath);
            $data = json_decode($json, true);
            
            if (!is_array($data)) {
                return null;
            }
            
            $provLower = strtolower(trim($prov));
            $kabLower = strtolower(trim($kab));
            $kecLower = strtolower(trim($kec));
            
            foreach ($data as $location) {
                $locProv = strtolower($location['provinsi'] ?? '');
                $locKab = strtolower($location['kabupaten'] ?? '');
                $locKec = strtolower($location['kecamatan'] ?? '');
                
                // Match exact atau partial
                if ($locProv === $provLower && 
                    $locKab === $kabLower && 
                    $locKec === $kecLower) {
                    
                    return [
                        'latitude'  => (float) $location['latitude'],
                        'longitude' => (float) $location['longitude'],
                        'provinsi' => $prov,
                        'kabupaten' => $kab,
                        'kecamatan' => $kec,
                        'display_name' => "{$location['kecamatan']}, {$location['kabupaten']}, {$location['provinsi']}, Indonesia",
                        'source' => 'fallback_json'
                    ];
                }
            }
            
            foreach ($data as $location) {
                $locProv = strtolower($location['provinsi'] ?? '');
                $locKab = strtolower($location['kabupaten'] ?? '');
                
                if ($locProv === $provLower && $locKab === $kabLower) {
                    return [
                        'latitude'  => (float) $location['latitude'],
                        'longitude' => (float) $location['longitude'],
                        'provinsi' => $prov,
                        'kabupaten' => $kab,
                        'kecamatan' => $kec,
                        'display_name' => "{$location['kabupaten']}, {$location['provinsi']}, Indonesia",
                        'source' => 'fallback_json_partial'
                    ];
                }
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error("Fallback file read error: {$e->getMessage()}");
            return null;
        }
    }
    
    /**
     * Clear cache untuk lokasi tertentu (untuk admin/refresh)
     */
    public function clearCache(string $prov, string $kab, string $kec): bool
    {
        $cacheKey = self::CACHE_PREFIX . md5("{$prov}|{$kab}|{$kec}");
        return Cache::forget($cacheKey);
    }
    
    /**
     * Clear semua cache geocoding (untuk maintenance)
     */
    public function clearAllCache(): void
    {
        Cache::flush(); 
        Log::info("All geocoding cache cleared");
    }
}