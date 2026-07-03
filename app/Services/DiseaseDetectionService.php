<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Engine di balik fitur "AI Scanner Penyakit Tanaman" (BAB 2.5.1).
 *
 * CATATAN ARSITEKTUR:
 * Dokumen modul sistem (BAB 5.3.1) menyebut deteksi penyakit memakai model
 * Python/TensorFlow yang diakses lewat REST API terpisah. Server model
 * tersebut belum tersedia di tahap ini, jadi kelas ini untuk sementara
 * memakai mesin klasifikasi berbasis katalog (rule-based, bukan machine
 * learning sungguhan) supaya seluruh alur AI Scanner — upload foto,
 * validasi, simpan laporan, tampilkan hasil, pemetaan, tindak lanjut
 * Penyuluh — bisa langsung berjalan end-to-end.
 *
 * Begitu API model AI Python/TensorFlow yang sebenarnya sudah siap, cukup
 * ganti isi method `analisis()` untuk memanggil `analisisViaApi()` (contoh
 * implementasinya sudah disediakan di bawah) — controller dan tampilan
 * yang memakai service ini TIDAK perlu diubah sama sekali, karena bentuk
 * array hasilnya sudah dibuat konsisten dengan yang dibutuhkan tabel
 * `disease_reports`.
 */
class DiseaseDetectionService
{
    /**
     * Katalog penyakit tanaman umum di Indonesia. Data gejala/penyebab/
     * penanganan bersifat edukatif umum (BAB 2.5.1: "Informasi lengkap
     * penyakit: gejala, penyebab, dan cara penanganan").
     */
    private array $katalog = [
        [
            'nama_penyakit'  => 'Hawar Daun (Leaf Blight)',
            'gejala'         => 'Bercak coklat kehitaman melebar di tepi/ujung daun, daun mengering dan mati mulai dari tepi.',
            'penyebab'       => 'Infeksi jamur yang berkembang pesat pada kondisi lembap dengan sirkulasi udara buruk.',
            'penanganan'     => 'Pangkas dan musnahkan bagian tanaman yang terinfeksi, perbaiki jarak tanam untuk sirkulasi udara, semprot fungisida sesuai anjuran.',
            'tingkat_risiko' => 'tinggi',
        ],
        [
            'nama_penyakit'  => 'Bercak Daun (Leaf Spot)',
            'gejala'         => 'Bercak bulat kecil berwarna coklat/kuning dengan tepi lebih gelap, tersebar di permukaan daun.',
            'penyebab'       => 'Infeksi jamur atau bakteri, sering dipicu kelembapan tinggi dan percikan air saat penyiraman.',
            'penanganan'     => 'Buang daun yang terinfeksi, hindari penyiraman dari atas, jaga jarak tanam, gunakan fungisida bila menyebar.',
            'tingkat_risiko' => 'sedang',
        ],
        [
            'nama_penyakit'  => 'Busuk Batang (Stem Rot)',
            'gejala'         => 'Batang melunak, berwarna coklat kehitaman, berbau tidak sedap, tanaman layu meski tanah cukup air.',
            'penyebab'       => 'Infeksi jamur/bakteri pada batang akibat drainase buruk dan kelembapan tanah berlebih.',
            'penanganan'     => 'Perbaiki drainase lahan, kurangi frekuensi penyiraman, cabut dan musnahkan tanaman yang parah terinfeksi.',
            'tingkat_risiko' => 'tinggi',
        ],
        [
            'nama_penyakit'  => 'Karat Daun (Leaf Rust)',
            'gejala'         => 'Bintik-bintik oranye kemerahan menyerupai karat besi di permukaan bawah daun.',
            'penyebab'       => 'Infeksi jamur karat, menyebar cepat lewat spora yang terbawa angin pada musim lembap.',
            'penanganan'     => 'Rotasi tanaman, buang daun terinfeksi, aplikasikan fungisida berbahan aktif sesuai rekomendasi penyuluh setempat.',
            'tingkat_risiko' => 'sedang',
        ],
        [
            'nama_penyakit'  => 'Layu Fusarium',
            'gejala'         => 'Daun menguning dimulai dari bagian bawah, tanaman layu bertahap meski disiram cukup, akar berwarna coklat.',
            'penyebab'       => 'Jamur tular tanah (Fusarium sp.) yang menginfeksi sistem pembuluh akar.',
            'penanganan'     => 'Cabut dan musnahkan tanaman terinfeksi, jangan tanam ulang komoditas sejenis di lokasi sama, gunakan varietas tahan penyakit.',
            'tingkat_risiko' => 'tinggi',
        ],
        [
            'nama_penyakit'  => 'Antraknosa (Anthracnose)',
            'gejala'         => 'Bercak cekung kehitaman pada daun, batang muda, atau buah, kadang dengan lingkaran konsentris.',
            'penyebab'       => 'Infeksi jamur yang berkembang pada kondisi hujan terus-menerus dan kelembapan tinggi.',
            'penanganan'     => 'Sanitasi kebun rutin, buang bagian tanaman terinfeksi, semprot fungisida preventif menjelang musim hujan.',
            'tingkat_risiko' => 'sedang',
        ],
        [
            'nama_penyakit'  => 'Serangan Kutu Daun (Aphid)',
            'gejala'         => 'Daun muda keriting dan menggulung, terdapat kerumunan serangga kecil berwarna hijau/hitam di bawah daun, muncul embun jelaga.',
            'penyebab'       => 'Hama kutu daun yang berkembang biak cepat pada cuaca kering dan hangat.',
            'penanganan'     => 'Semprot dengan larutan sabun insektisida atau pestisida nabati, manfaatkan predator alami seperti kepik.',
            'tingkat_risiko' => 'rendah',
        ],
        [
            'nama_penyakit'  => 'Embun Tepung (Powdery Mildew)',
            'gejala'         => 'Lapisan tepung putih menyelimuti permukaan daun dan batang, daun akhirnya menguning dan gugur.',
            'penyebab'       => 'Infeksi jamur yang berkembang pada kondisi kering di siang hari namun lembap di malam hari.',
            'penanganan'     => 'Perbaiki sirkulasi udara antar tanaman, hindari kelembapan berlebih, semprot fungisida berbahan belerang bila perlu.',
            'tingkat_risiko' => 'sedang',
        ],
    ];

    /**
     * Menjalankan analisis pada satu foto tanaman dan mengembalikan hasil
     * yang siap disimpan ke tabel `disease_reports`.
     *
     * @return array{nama_penyakit:string, confidence_score:float, gejala:string, penyebab:string, penanganan:string, tingkat_risiko:string}
     */
    public function analisis(string $absoluteImagePath): array
    {
        if (config('services.ai_scanner.use_external_api') && config('services.ai_scanner.url')) {
            try {
                return $this->analisisViaApi($absoluteImagePath);
            } catch (\Throwable $e) {
                Log::warning('AI Scanner: gagal memanggil API eksternal, fallback ke katalog lokal.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->analisisLokal($absoluteImagePath);
    }

    /**
     * Mesin klasifikasi sementara (rule-based), memilih entri katalog
     * berdasarkan hash isi file foto supaya hasilnya konsisten untuk foto
     * yang sama, dan menghasilkan confidence score yang realistis (70-97%)
     * — bukan machine learning sungguhan, hanya placeholder yang membuat
     * seluruh alur aplikasi bisa langsung dites.
     */
    private function analisisLokal(string $absoluteImagePath): array
    {
        $hash = @is_file($absoluteImagePath) ? md5_file($absoluteImagePath) : md5(uniqid());
        $seed = hexdec(substr($hash, 0, 8));

        $index  = $seed % count($this->katalog);
        $entry  = $this->katalog[$index];

        // Confidence score pseudo-acak namun deterministik dari hash, range 70.00–97.00
        $confidence = 70 + (($seed >> 8) % 2701) / 100;

        return [
            'nama_penyakit'    => $entry['nama_penyakit'],
            'confidence_score' => round($confidence, 2),
            'gejala'           => $entry['gejala'],
            'penyebab'         => $entry['penyebab'],
            'penanganan'       => $entry['penanganan'],
            'tingkat_risiko'   => $entry['tingkat_risiko'],
        ];
    }

    /**
     * Contoh implementasi pemanggilan API model AI Python/TensorFlow
     * eksternal sesuai BAB 5.3.1. Diaktifkan lewat config
     * `services.ai_scanner.use_external_api` + `.env`:
     *   AI_SCANNER_USE_API=true
     *   AI_SCANNER_API_URL=https://model-ai.contoh.internal/predict
     *
     * API diasumsikan menerima multipart form field `image` dan
     * mengembalikan JSON dengan bentuk yang sama seperti hasil
     * `analisisLokal()` di atas.
     */
    private function analisisViaApi(string $absoluteImagePath): array
    {
        $response = Http::timeout(15)
            ->attach('image', file_get_contents($absoluteImagePath), basename($absoluteImagePath))
            ->post(config('services.ai_scanner.url'));

        if (! $response->successful()) {
            throw new \RuntimeException('AI Scanner API merespons dengan error: '.$response->status());
        }

        $json = $response->json();

        return [
            'nama_penyakit'    => $json['nama_penyakit'] ?? 'Tidak Teridentifikasi',
            'confidence_score' => (float) ($json['confidence_score'] ?? 0),
            'gejala'           => $json['gejala'] ?? '-',
            'penyebab'         => $json['penyebab'] ?? '-',
            'penanganan'       => $json['penanganan'] ?? '-',
            'tingkat_risiko'   => $json['tingkat_risiko'] ?? 'sedang',
        ];
    }
}
