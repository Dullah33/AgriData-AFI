<?php

namespace Database\Seeders;

use App\Models\Plant;
use Illuminate\Database\Seeder;

class PlantSeeder extends Seeder
{
    public function run(): void
    {
        // Data tanaman yang akan di-seed
        $plants = [
            [
                'kode' => '01',
                'nama' => 'Padi',
                'gambar' => 'images/tanaman/padi.png',
                'suhu_ideal' => '24-30°C',
                'min_suhu' => 24.00,
                'max_suhu' => 30.00,
                'kelembapan_ideal' => '70-85%',
                'min_kelembaban' => 70.00,
                'max_kelembaban' => 85.00,
                'curah_hujan_ideal' => 'Tinggi',
                'musim' => 'Musim Hujan',
                'jenis_tanah' => 'Lempung',
                'status_cuaca_hujan' => 'Waktu ideal untuk penanaman. Pastikan drainase baik.',
                'status_cuaca_panas' => 'Tunda tanam. Risiko puso tinggi akibat kekeringan.',
                'status_curah_hujan_tinggi' => 'Optimal. Lanjutkan pemupukan dan pengendalian hama.',
                'status_curah_hujan_rendah' => 'Kritis. Siapkan irigasi tambahan atau tunda.',
                'deskripsi' => 'Tanaman pangan utama Indonesia. Membutuhkan penggenangan air pada fase vegetatif.',
                'keunggulan' => 'Produktivitas tinggi dan merupakan sumber karbohidrat utama.',
                'durasi_panen' => '3-4 bulan'
            ],
            [
                'kode' => '02',
                'nama' => 'Jagung',
                'gambar' => 'images/tanaman/jagung.png',
                'suhu_ideal' => '25-30°C',
                'min_suhu' => 25.00,
                'max_suhu' => 30.00,
                'kelembapan_ideal' => '60-75%',
                'min_kelembaban' => 60.00,
                'max_kelembaban' => 75.00,
                'curah_hujan_ideal' => 'Sedang',
                'musim' => 'Musim Kemarau',
                'jenis_tanah' => 'Lempung Berpasir',
                'status_cuaca_hujan' => 'Waspadai genangan air. Jagung rentan busuk akar.',
                'status_cuaca_panas' => 'Kondisi optimal. Pastikan pemupukan nitrogen tercukupi.',
                'status_curah_hujan_tinggi' => 'Berisiko tinggi. Lakukan perbaikan drainase segera.',
                'status_curah_hujan_rendah' => 'Cocok. Lakukan irigasi tetes jika tersedia.',
                'deskripsi' => 'Tanaman serbaguna untuk pangan dan pakan ternak. Tahan kondisi kering relatif baik.',
                'keunggulan' => 'Perawatan relatif mudah dan harga pasar stabil.',
                'durasi_panen' => '2.5-3 bulan'
            ],
            [
                'kode' => '03',
                'nama' => 'Cabai Rawit',
                'gambar' => 'images/tanaman/cabai.png',
                'suhu_ideal' => '25-27°C',
                'min_suhu' => 25.00,
                'max_suhu' => 27.00,
                'kelembapan_ideal' => '60-80%',
                'min_kelembaban' => 60.00,
                'max_kelembaban' => 80.00,
                'curah_hujan_ideal' => 'Rendah',
                'musim' => 'Musim Kemarau',
                'jenis_tanah' => 'Gembur & Subur',
                'status_cuaca_hujan' => 'Rentan penyakit jamur. Gunakan mulsa plastik.',
                'status_cuaca_panas' => 'Sangat ideal. Tingkatkan frekuensi panen.',
                'status_curah_hujan_tinggi' => 'Hindari tanam. Risiko busuk buah dan ranting sangat tinggi.',
                'status_curah_hujan_rendah' => 'Waktu terbaik. Fokus pada pengendalian kutu daun.',
                'deskripsi' => 'Komoditas hortikultura bernilai ekonomi tinggi. Sensitif terhadap curah hujan berlebih.',
                'keunggulan' => 'Permintaan pasar selalu tinggi dan harga fluktuatif menguntungkan.',
                'durasi_panen' => '2-3 bulan (panen berkelanjutan)'
            ],
            [
                'kode' => '04',
                'nama' => 'Kedelai',
                'gambar' => 'images/tanaman/kedelai.png',
                'suhu_ideal' => '21-30°C',
                'min_suhu' => 21.00,
                'max_suhu' => 30.00,
                'kelembapan_ideal' => '65-75%',
                'min_kelembaban' => 65.00,
                'max_kelembaban' => 75.00,
                'curah_hujan_ideal' => 'Sedang',
                'musim' => 'Musim Kemarau',
                'jenis_tanah' => 'Lempung Berliat',
                'status_cuaca_hujan' => 'Tunda. Tanah basah memicu hama lalat bibit.',
                'status_cuaca_panas' => 'Ideal. Bantu dengan irigasi hemat air.',
                'status_curah_hujan_tinggi' => 'Berisiko gagal panen. Lakukan drainase ekstra.',
                'status_curah_hujan_rendah' => 'Cocok. Lakukan pengairan saat fase pembungaan.',
                'deskripsi' => 'Sumber protein nabati utama. Membutuhkan drainase baik karena rentan genangan.',
                'keunggulan' => 'Umur pendek dan dapat ditanam sebagai tanaman sela.',
                'durasi_panen' => '2-2.5 bulan'
            ],
            [
                'kode' => '05',
                'nama' => 'Tomat',
                'gambar' => 'images/tanaman/tomat.png',
                'suhu_ideal' => '20-25°C',
                'min_suhu' => 20.00,
                'max_suhu' => 25.00,
                'kelembapan_ideal' => '60-70%',
                'min_kelembaban' => 60.00,
                'max_kelembaban' => 70.00,
                'curah_hujan_ideal' => 'Rendah',
                'musim' => 'Musim Kemarau',
                'jenis_tanah' => 'Gembur & Organik',
                'status_cuaca_hujan' => 'Hindari. Curah hujan memicu penyakit layu dan bercak daun.',
                'status_cuaca_panas' => 'Optimal. Gunakan naungan jika suhu >30°C.',
                'status_curah_hujan_tinggi' => 'Sangat tidak disarankan. Risiko panen nol tinggi.',
                'status_curah_hujan_rendah' => 'Waktu terbaik. Pastikan ketersediaan air teratur.',
                'deskripsi' => 'Tanaman hortikultura populer. Sensitif terhadap suhu tinggi dan kelembaban berlebih.',
                'keunggulan' => 'Nilai jual tinggi dan dapat diolah menjadi berbagai produk turunan.',
                'durasi_panen' => '2.5-3 bulan'
            ]
        ];

        foreach ($plants as $plant) {
            Plant::updateOrCreate(
                ['kode' => $plant['kode']], 
                $plant                      
            );
        }

        $this->command->info('5 Data Tanaman berhasil di-seed!');
    }
}