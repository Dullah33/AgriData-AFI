<?php

namespace Database\Seeders;

use App\Models\Plant;
use Illuminate\Database\Seeder;

class PlantSeeder extends Seeder
{
    public function run(): void
    {
        $plants = [
            [
                'kode' => '01',
                'nama' => 'Padi',
                'gambar' => 'assets/images/tanaman/Padi.jpg',
                'suhu_ideal' => '24-30°C',
                'min_suhu' => 24.00,
                'max_suhu' => 30.00,
                'kelembapan_ideal' => '70-85%',
                'min_kelembaban' => 70.00,
                'max_kelembaban' => 85.00,
                'curah_hujan_ideal' => 'Tinggi',
                'musim' => 'Musim Hujan',
                'jenis_tanah' => 'Lempung',
                'status_cuaca_hujan' => 'Waktu ideal untuk penanaman.',
                'status_cuaca_panas' => 'Tunda tanam.',
                'status_curah_hujan_tinggi' => 'Waspada genangan air. Pastikan saluran drainase lancar untuk mencegah busuk akar dan penyakit jamur.',
                'status_curah_hujan_rendah' => 'Siapkan irigasi tambahan. Curah hujan tidak mencukupi, lakukan penyiraman rutin pagi dan sore hari.',
                'deskripsi' => 'Tanaman pangan utama Indonesia.',
                'keunggulan' => 'Produktivitas tinggi.',
                'durasi_panen' => '3-4 bulan',
                
                // DATA BUDIDAYA
                'budidaya_persiapan_lahan' => 'Bersihkan lahan dari gulma. Cangkul tanah sedalam 20-30 cm hingga gembur. Buat saluran irigasi dan bedengan dengan lebar 1-1.2 meter.',
                'budidaya_pemupukan' => 'Berikan pupuk dasar (urea, SP-36, KCl) sesuai dosis rekomendasi. Tambahkan pupuk organik atau kompos 2 minggu sebelum tanam.',
                'budidaya_irigasi' => 'Sistem irigasi penggenangan untuk padi sawah. Pastikan tinggi genangan air terjaga 5-10 cm pada fase vegetatif.',
                
                'langkah_budidaya' => json_encode([
                    ['title' => 'Persiapan Bibit', 'content' => 'Rendam benih padi selama 24 jam, tiriskan, lalu kecambahkan di karung goni lembab selama 2-3 hari.'],
                    ['title' => 'Pengolahan Tanah', 'content' => 'Lakukan bajak dan garu sebanyak 2-3 kali hingga tanah berlumpur rata.'],
                    ['title' => 'Penyemaian', 'content' => 'Taburkan benih kecambah di bedengan semai. Siram secara rutin hingga bibit berumur 15-20 hari.'],
                    ['title' => 'Penanaman', 'content' => 'Pindahkan bibit ke lahan sawah dengan sistem tegel atau tanam baris (jarak 25x25 cm).'],
                    ['title' => 'Pemeliharaan', 'content' => 'Lakukan penyulaman, penyiangan, dan pengendalian hama secara berkala.'],
                    ['title' => 'Panen', 'content' => 'Panen saat 80-90% bulir padi menguning (sekitar 30 hari setelah berbunga).'],
                ]),
                'tips_budidaya' => json_encode([
                    ['icon' => '☀️', 'title' => 'Penyinaran', 'content' => 'Padi membutuhkan sinar matahari penuh minimal 8 jam per hari.'],
                    ['icon' => '💧', 'title' => 'Pengairan', 'content' => 'Jangan sampai lahan kering saat fase pembungaan.'],
                    ['icon' => '🐛', 'title' => 'Hama', 'content' => 'Waspadai serangan wereng coklat dan penggerek batang.'],
                ])
            ],
            [
                'kode' => '02',
                'nama' => 'Jagung',
                'gambar' => 'assets/images/tanaman/Jagung.jpg',
                'suhu_ideal' => '25-30°C',
                'min_suhu' => 25.00,
                'max_suhu' => 30.00,
                'kelembapan_ideal' => '60-75%',
                'min_kelembaban' => 60.00,
                'max_kelembaban' => 75.00,
                'curah_hujan_ideal' => 'Sedang',
                'musim' => 'Musim Kemarau',
                'jenis_tanah' => 'Lempung Berpasir',
                'status_cuaca_hujan' => 'Waspadai genangan air.',
                'status_cuaca_panas' => 'Kondisi optimal.',
                'status_curah_hujan_tinggi' => 'Perhatikan drainase. Jagung rentan busuk akar jika tergenang air terlalu lama.',
                'status_curah_hujan_rendah' => 'Kebutuhan air tinggi. Gunakan sistem irigasi tetes untuk efisiensi air.',
                'deskripsi' => 'Tanaman serbaguna untuk pangan dan pakan.',
                'keunggulan' => 'Perawatan relatif mudah.',
                'durasi_panen' => '2.5-3 bulan',
                
                'budidaya_persiapan_lahan' => 'Olah tanah hingga gembur sedalam 20 cm. Buat bedengan dengan lebar 1 meter dan tinggi 20 cm.',
                'budidaya_pemupukan' => 'Berikan pupuk dasar NPK 15-15-15 sebanyak 200 kg/ha. Tambahkan pupuk susulan urea pada umur 21 dan 35 hari.',
                'budidaya_irigasi' => 'Siram secara teratur terutama pada fase pertumbuhan vegetatif dan pembungaan.',
                
                'langkah_budidaya' => json_encode([
                    ['title' => 'Persiapan Benih', 'content' => 'Pilih benih jagung hibrida unggul. Rendam benih dalam air hangat selama 2 jam sebelum tanam.'],
                    ['title' => 'Penanaman', 'content' => 'Tanam benih dengan jarak 75x25 cm. Masukkan 2 benih per lubang tanam sedalam 3-5 cm.'],
                    ['title' => 'Penyulaman', 'content' => 'Ganti benih yang tidak tumbuh pada umur 7-10 hari setelah tanam.'],
                    ['title' => 'Penyiangan', 'content' => 'Bersihkan gulma pada umur 15 dan 30 hari setelah tanam.'],
                    ['title' => 'Pemupukan Susulan', 'content' => 'Berikan urea 100 kg/ha pada umur 21 hari dan 100 kg/ha pada umur 35 hari.'],
                    ['title' => 'Panen', 'content' => 'Panen saat kelobot mengering dan biji jagung mengeras (umur 100-110 hari).'],
                ]),
                'tips_budidaya' => json_encode([
                    ['icon' => '🌽', 'title' => 'Jarak Tanam', 'content' => 'Jarak tanam yang tepat mencegah persaingan nutrisi.'],
                    ['icon' => '🌱', 'title' => 'Rotasi', 'content' => 'Lakukan rotasi tanaman untuk menjaga kesuburan tanah.'],
                ])
            ],
            [
                'kode' => '03',
                'nama' => 'Cabai',
                'gambar' => 'assets/images/tanaman/Cabai.jpg',
                'suhu_ideal' => '25-27°C',
                'min_suhu' => 25.00,
                'max_suhu' => 27.00,
                'kelembapan_ideal' => '60-80%',
                'min_kelembaban' => 60.00,
                'max_kelembaban' => 80.00,
                'curah_hujan_ideal' => 'Rendah',
                'musim' => 'Musim Kemarau',
                'jenis_tanah' => 'Gembur & Subur',
                'status_cuaca_hujan' => 'Rentan penyakit jamur.',
                'status_cuaca_panas' => 'Sangat ideal.',
                'status_curah_hujan_tinggi' => 'Risiko penyakit tinggi. Lindungi tanaman dengan mulsa plastik dan hindari penyiraman berlebihan.',
                'status_curah_hujan_rendah' => 'Waspada kekeringan. Cabai sensitif terhadap kekurangan air, terutama saat pembungaan.',
                'deskripsi' => 'Komoditas hortikultura bernilai ekonomi tinggi.',
                'keunggulan' => 'Permintaan pasar selalu tinggi.',
                'durasi_panen' => '2-3 bulan',
                
                'budidaya_persiapan_lahan' => 'Cangkul tanah sedalam 30 cm. Buat bedengan lebar 100-120 cm, tinggi 30 cm. Berikan pupuk kandang 20 ton/ha.',
                'budidaya_pemupukan' => 'Berikan NPK 15-15-15 sebanyak 300 kg/ha. Tambahkan pupuk kandang cair setiap 2 minggu.',
                'budidaya_irigasi' => 'Siram setiap hari pada musim kemarau. Gunakan mulsa plastik untuk menjaga kelembapan.',
                
                'langkah_budidaya' => json_encode([
                    ['title' => 'Penyemaian', 'content' => 'Semai benih cabai dalam tray semai. Siram rutin hingga bibit berumur 30-40 hari.'],
                    ['title' => 'Persiapan Lahan', 'content' => 'Buat bedengan dengan mulsa plastik hitam perak. Pasang ajir bambu setinggi 1 meter.'],
                    ['title' => 'Penanaman', 'content' => 'Tanam bibit pada sore hari dengan jarak 70x60 cm. Siram segera setelah tanam.'],
                    ['title' => 'Pemeliharaan', 'content' => 'Ikat tanaman pada ajir. Pangkas tunas samping hingga umur 45 hari.'],
                    ['title' => 'Pengendalian Hama', 'content' => 'Semprot pestisida organik setiap minggu. Waspadai kutu daun dan thrips.'],
                    ['title' => 'Panen', 'content' => 'Panen pertama umur 75-85 hari. Panen berlanjut setiap 3-4 hari sekali.'],
                ]),
                'tips_budidaya' => json_encode([
                    ['icon' => '🌶️', 'title' => 'Mulsa', 'content' => 'Gunakan mulsa plastik untuk hasil optimal.'],
                    ['icon' => '🌡️', 'title' => 'Suhu', 'content' => 'Cabai tumbuh optimal di dataran rendah hingga menengah.'],
                ])
            ],
            [
                'kode' => '04',
                'nama' => 'Tomat',
                'gambar' => 'assets/images/tanaman/Tomat.jpg',
                'suhu_ideal' => '20-25°C',
                'min_suhu' => 20.00,
                'max_suhu' => 25.00,
                'kelembapan_ideal' => '60-70%',
                'min_kelembaban' => 60.00,
                'max_kelembaban' => 70.00,
                'curah_hujan_ideal' => 'Rendah',
                'musim' => 'Musim Kemarau',
                'jenis_tanah' => 'Gembur & Organik',
                'status_cuaca_hujan' => 'Hindari. Curah hujan memicu penyakit.',
                'status_cuaca_panas' => 'Optimal.',
                'status_curah_hujan_tinggi' => 'Lindungi tanaman. Curah hujan tinggi memicu penyakit layu dan bercak daun pada tomat.',
                'status_curah_hujan_rendah' => 'Gunakan mulsa. Tomat butuh kelembapan stabil, gunakan mulsa untuk mengurangi penguapan.',
                'deskripsi' => 'Tanaman hortikultura populer untuk sayuran dan buah.',
                'keunggulan' => 'Nilai jual tinggi.',
                'durasi_panen' => '2.5-3 bulan',
                
                'budidaya_persiapan_lahan' => 'Olah tanah gembur sedalam 30 cm. Buat bedengan lebar 100 cm, tinggi 40 cm. Berikan kompos 10 ton/ha.',
                'budidaya_pemupukan' => 'Berikan NPK 16-16-16 sebanyak 250 kg/ha. Tambahkan pupuk organik cair setiap minggu.',
                'budidaya_irigasi' => 'Siram setiap pagi dan sore. Jangan biarkan tanah terlalu basah.',
                
                'langkah_budidaya' => json_encode([
                    ['title' => 'Penyemaian', 'content' => 'Semai benih tomat dalam polybag kecil. Rawat hingga bibit berumur 30-35 hari.'],
                    ['title' => 'Penanaman', 'content' => 'Tanam bibit dengan jarak 60x70 cm. Siram segera setelah tanam.'],
                    ['title' => 'Pemasangan Ajir', 'content' => 'Pasang ajir bambu setinggi 1.5 meter untuk menopang tanaman.'],
                    ['title' => 'Pemangkasan', 'content' => 'Pangkas tunas samping setiap minggu. Sisakan 2-3 batang utama.'],
                    ['title' => 'Pemupukan', 'content' => 'Berikan pupuk NPK setiap 2 minggu. Tambahkan pupuk kandang cair.'],
                    ['title' => 'Panen', 'content' => 'Panen saat buah berwarna merah sempurna (umur 60-75 hari).'],
                ]),
                'tips_budidaya' => json_encode([
                    ['icon' => '🍅', 'title' => 'Panen', 'content' => 'Panen pagi hari untuk kesegaran optimal.'],
                    ['icon' => '🌿', 'title' => 'Sirkulasi', 'content' => 'Jaga sirkulasi udara dengan jarak tanam yang tepat.'],
                ])
            ],
            [
                'kode' => '05',
                'nama' => 'Wortel',
                'gambar' => 'assets/images/tanaman/Wortel.jpg',
                'suhu_ideal' => '15-21°C',
                'min_suhu' => 15.00,
                'max_suhu' => 21.00,
                'kelembapan_ideal' => '65-75%',
                'min_kelembaban' => 65.00,
                'max_kelembaban' => 75.00,
                'curah_hujan_ideal' => 'Sedang',
                'musim' => 'Musim Hujan',
                'jenis_tanah' => 'Gembur & Berpasir',
                'status_cuaca_hujan' => 'Waktu ideal.',
                'status_cuaca_panas' => 'Kurang ideal.',
                'status_curah_hujan_tinggi' => 'Tunda kegiatan tanam. Tanah terlalu basah untuk perkecambahan benih kedelai.',
                'status_curah_hujan_rendah' => 'Siapkan irigasi tambahan. Kedelai butuh air cukup saat fase pembungaan dan pengisian polong.',
                'deskripsi' => 'Sayuran umbi kaya vitamin A dan beta karoten.',
                'keunggulan' => 'Tahan simpan lama.',
                'durasi_panen' => '3-4 bulan',
                
                'budidaya_persiapan_lahan' => 'Cangkul tanah hingga gembur sedalam 40 cm. Buat bedengan lebar 100 cm. Wortel butuh tanah gembur.',
                'budidaya_pemupukan' => 'Berikan pupuk kandang 15 ton/ha. Tambahkan NPK 100 kg/ha.',
                'budidaya_irigasi' => 'Siram secara teratur. Jaga tanah tetap lembap tapi tidak becek.',
                
                'langkah_budidaya' => json_encode([
                    ['title' => 'Persiapan Benih', 'content' => 'Rendam benih wortel dalam air hangat selama 2 jam. Tiriskan sebelum tanam.'],
                    ['title' => 'Penanaman', 'content' => 'Tabur benih secara merata di bedengan. Tutup tipis dengan tanah 1-2 cm.'],
                    ['title' => 'Penjarangan', 'content' => 'Jarangkan tanaman pada umur 15 hari. Jarak ideal 5-10 cm.'],
                    ['title' => 'Penyiangan', 'content' => 'Bersihkan gulma secara rutin. Wortel sangat sensitif terhadap gulma.'],
                    ['title' => 'Pembumbunan', 'content' => 'Timbun pangkal umbi dengan tanah agar tidak hijau.'],
                    ['title' => 'Panen', 'content' => 'Panen umur 90-120 hari. Cabut umbi dengan hati-hati.'],
                ]),
                'tips_budidaya' => json_encode([
                    ['icon' => '🥕', 'title' => 'Tanah', 'content' => 'Tanah harus gembur untuk umbi sempurna.'],
                    ['icon' => '💧', 'title' => 'Air', 'content' => 'Penyiraman teratur mencegah umbi pecah.'],
                ])
            ],
        ];

        foreach ($plants as $plantData) {
            Plant::updateOrCreate(['kode' => $plantData['kode']], $plantData);
        }

        $this->command->info('✅ Data Budidaya berhasil ditambahkan untuk 5 tanaman!');
    }
}