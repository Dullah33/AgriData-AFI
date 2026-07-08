<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Melengkapi field "penyebab" dan "penanganan" hasil deteksi Plant.id
 * ketika API tersebut tidak menyediakan data untuk kelas penyakit
 * tertentu (lihat contoh kasus "Diplocarpon" — penyebab kosong,
 * penanganan cuma "Tidak ada rekomendasi penanganan spesifik...").
 *
 * Dipanggil dari PlantIdService HANYA saat field terkait masih kosong/
 * default, supaya tidak menambah API call kalau Plant.id sudah lengkap.
 *
 * Pakai Google Gemini API (gemini-2.0-flash) — punya free tier tanpa
 * kartu kredit. Ambil API key gratis di https://aistudio.google.com/apikey
 */
class GeminiEnricherService
{
    private const ENDPOINT_TEMPLATE = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    /**
     * @return array{penyebab: ?string, penanganan: ?string} null kalau gagal/tidak terkonfigurasi
     */
    public function lengkapi(string $namaPenyakit, string $gejala): ?array
    {
        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            // Belum dikonfigurasi — jangan error, cukup skip enrichment.
            return null;
        }

        $model = config('services.gemini.model', 'gemini-2.0-flash');
        $endpoint = sprintf(self::ENDPOINT_TEMPLATE, $model);

        $prompt = <<<PROMPT
        Kamu adalah asisten pertanian untuk petani Indonesia. Diberikan nama penyakit/hama tanaman dan gejalanya, jelaskan singkat dalam Bahasa Indonesia:
        1. Penyebab penyakit tersebut (1-2 kalimat).
        2. Cara penanganan/pengendalian yang praktis untuk petani (1-3 kalimat).

        Nama penyakit: {$namaPenyakit}
        Gejala: {$gejala}

        Jawab HANYA dalam format JSON tanpa markdown, tanpa penjelasan tambahan, persis seperti ini:
        {"penyebab": "...", "penanganan": "..."}
        PROMPT;

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->withQueryParameters(['key' => $apiKey])
                ->post($endpoint, [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 300,
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('GeminiEnricher: API merespons error.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            if (empty($text)) {
                return null;
            }

            // Bersihkan kemungkinan markdown code fence (```json ... ```)
            $bersih = trim(preg_replace('/^```json|```$/m', '', $text));

            $parsed = json_decode($bersih, true);

            if (! is_array($parsed) || empty($parsed['penyebab']) && empty($parsed['penanganan'])) {
                return null;
            }

            return [
                'penyebab' => $parsed['penyebab'] ?? null,
                'penanganan' => $parsed['penanganan'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::warning('GeminiEnricher: gagal memanggil Gemini API.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}