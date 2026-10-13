<?php

namespace Tests\Feature;

use App\Models\DiseaseReport;
use App\Models\Lahan;
use App\Models\PetaniProfile;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ScanPenyakitTest extends TestCase
{
    use RefreshDatabase;

    private const URL_GEMINI = 'generativelanguage.googleapis.com/*';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.ai_scanner.provider' => 'gemini',
            'services.gemini.api_key'      => 'kunci-test',
            'services.gemini.model'        => 'gemini-flash-latest',
        ]);

        Storage::fake('public');
        Sleep::fake();
    }

    public function test_halaman_scanner_menampilkan_pilihan_kamera_dan_galeri(): void
    {
        $this->actingAs($this->buatUser('user'))
            ->get(route('user.deteksi-penyakit.create'))
            ->assertOk()
            ->assertSee('Unggah dari Galeri')
            ->assertSee('Ambil dari Kamera')
            ->assertSee('assets/js/scanner-penyakit.js', false);

        $this->actingAs($this->buatPetani()['user'])
            ->get(route('petani.deteksi-penyakit.create'))
            ->assertOk()
            ->assertSee('Ambil dari Kamera')
            ->assertSee('Lahan Terkait (opsional)');
    }

    public function test_user_bisa_scan_dan_hasil_gemini_tersimpan_beserta_fotonya(): void
    {
        Http::fake([self::URL_GEMINI => Http::response($this->responsGemini())]);

        $response = $this->actingAs($this->buatUser('user'))
            ->postJson(route('user.deteksi-penyakit.store'), [
                'foto_tanaman' => UploadedFile::fake()->image('daun-cabai.jpg', 800, 600),
            ]);

        $laporan = DiseaseReport::sole();

        $response->assertCreated()
            ->assertJsonPath('redirect', route('user.deteksi-penyakit.show', $laporan));

        $this->assertSame('Antraknosa (Colletotrichum capsici)', $laporan->nama_penyakit);
        $this->assertSame('87.50', $laporan->confidence_score);
        $this->assertSame('tinggi', $laporan->tingkat_risiko);
        $this->assertStringContainsString("1. Petik dan musnahkan buah yang terinfeksi.\n2.", $laporan->penanganan);
        $this->assertSame('baru', $laporan->status);
        $this->assertSame('gemini', $laporan->hasil_ai['provider']);
        $this->assertSame('gemini-3.5-flash', $laporan->hasil_ai['model']);
        $this->assertSame('Cabai', $laporan->namaTanamanTerdeteksi());
        Storage::disk('public')->assertExists($laporan->foto_tanaman);
        $this->assertStringStartsWith('deteksi-penyakit/', $laporan->foto_tanaman);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), '/models/gemini-flash-latest:generateContent')
                && $request->hasHeader('x-goog-api-key', 'kunci-test')
                && ! str_contains($request->url(), 'key=')
                && str_contains($request['system_instruction']['parts'][0]['text'], 'ahli patologi tanaman')
                && $request['contents'][0]['parts'][0]['inline_data']['mime_type'] === 'image/jpeg'
                && $request['generationConfig']['responseMimeType'] === 'application/json'
                && isset($request['generationConfig']['responseSchema']['properties']['solusi_penanganan']);
        });
    }

    public function test_form_tanpa_javascript_tetap_diarahkan_ke_halaman_hasil(): void
    {
        Http::fake([self::URL_GEMINI => Http::response($this->responsGemini())]);

        $response = $this->actingAs($this->buatUser('user'))
            ->post(route('user.deteksi-penyakit.store'), [
                'foto_tanaman' => UploadedFile::fake()->image('daun.png', 640, 480),
            ]);

        $response->assertRedirect(route('user.deteksi-penyakit.show', DiseaseReport::sole()))
            ->assertSessionHas('success');
    }

    public function test_halaman_hasil_menampilkan_diagnosis(): void
    {
        Http::fake([self::URL_GEMINI => Http::response($this->responsGemini())]);
        $user = $this->buatUser('user');

        $this->actingAs($user)->postJson(route('user.deteksi-penyakit.store'), [
            'foto_tanaman' => UploadedFile::fake()->image('daun.jpg', 640, 480),
        ]);

        $this->actingAs($user)
            ->get(route('user.deteksi-penyakit.show', DiseaseReport::sole()))
            ->assertOk()
            ->assertSee('Antraknosa (Colletotrichum capsici)')
            ->assertSee('Tanaman terdeteksi:')
            ->assertSee('Tingkat akurasi: 87.50%');
    }

    #[DataProvider('fileTidakValid')]
    public function test_validasi_menolak_file_yang_tidak_memenuhi_syarat(callable $buatFile, string $pesan): void
    {
        Http::fake();

        $this->actingAs($this->buatUser('user'))
            ->postJson(route('user.deteksi-penyakit.store'), ['foto_tanaman' => $buatFile()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['foto_tanaman' => $pesan]);

        Http::assertNothingSent();
        $this->assertSame(0, DiseaseReport::count());
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public static function fileTidakValid(): array
    {
        return [
            'tanpa foto' => [fn () => null, 'wajib diunggah'],
            'pdf' => [fn () => UploadedFile::fake()->create('laporan.pdf', 120, 'application/pdf'), 'harus berupa gambar'],
            'teks berekstensi jpg' => [fn () => self::fileTeksBerekstensiJpg(), 'harus berupa gambar'],
            'gif' => [fn () => UploadedFile::fake()->image('animasi.gif', 400, 400), 'JPG, PNG, atau WEBP'],
            'lebih dari 5 MB' => [fn () => UploadedFile::fake()->image('besar.jpg', 1200, 900)->size(5121), 'maksimal 5 MB'],
            'resolusi terlalu kecil' => [fn () => UploadedFile::fake()->image('kecil.jpg', 120, 120), 'terlalu kecil'],
        ];
    }

    /**
     * File teks biasa yang diberi nama .jpg. Sengaja tidak memakai
     * UploadedFile::fake() karena file palsu Laravel menebak MIME dari nama
     * file, sedangkan upload asli dicek dari isi file.
     */
    private static function fileTeksBerekstensiJpg(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'scan');
        file_put_contents($path, 'ini bukan gambar sungguhan');

        return new UploadedFile($path, 'palsu.jpg', null, null, true);
    }

    public function test_foto_bukan_tanaman_ditolak_tanpa_menyimpan_laporan_maupun_foto(): void
    {
        Http::fake([self::URL_GEMINI => Http::response($this->responsGemini([
            'is_tanaman' => false, 'nama_tanaman' => '-', 'gejala' => '-', 'nama_penyakit' => '-',
            'penyebab' => '-', 'tingkat_akurasi' => 0, 'tingkat_risiko' => 'rendah', 'solusi_penanganan' => '-',
        ]))]);

        $this->actingAs($this->buatUser('user'))
            ->postJson(route('user.deteksi-penyakit.store'), [
                'foto_tanaman' => UploadedFile::fake()->image('kucing.jpg', 640, 480),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['foto_tanaman' => 'bukan tanaman']);

        $this->assertSame(0, DiseaseReport::count());
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_gemini_gangguan_tidak_menghasilkan_diagnosis_palsu(): void
    {
        Http::fake([self::URL_GEMINI => Http::response(['error' => ['message' => 'The model is overloaded.']], 503)]);

        $this->actingAs($this->buatUser('user'))
            ->postJson(route('user.deteksi-penyakit.store'), [
                'foto_tanaman' => UploadedFile::fake()->image('daun.jpg', 640, 480),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['foto_tanaman' => 'sedang tidak bisa dihubungi']);

        // 1 percobaan awal + 2 kali ulang
        Http::assertSentCount(3);
        $this->assertSame(0, DiseaseReport::count());
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_api_key_gemini_belum_diatur_memberi_pesan_yang_jelas(): void
    {
        config(['services.gemini.api_key' => null]);
        Http::fake();

        $this->actingAs($this->buatUser('user'))
            ->postJson(route('user.deteksi-penyakit.store'), [
                'foto_tanaman' => UploadedFile::fake()->image('daun.jpg', 640, 480),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['foto_tanaman' => 'sedang tidak bisa dihubungi']);

        Http::assertNothingSent();
    }

    public function test_petani_scan_terhubung_ke_lahan_miliknya(): void
    {
        Http::fake([self::URL_GEMINI => Http::response($this->responsGemini())]);
        ['user' => $petani, 'lahan' => $lahan, 'profil' => $profil] = $this->buatPetani();

        $this->actingAs($petani)
            ->postJson(route('petani.deteksi-penyakit.store'), [
                'foto_tanaman' => UploadedFile::fake()->image('daun.jpg', 800, 600),
                'lahan_id'     => $lahan->id,
            ])
            ->assertCreated();

        $laporan = DiseaseReport::sole();
        $this->assertSame($lahan->id, $laporan->lahan_id);
        $this->assertSame($profil->wilayah_id, $laporan->wilayah_id);
        // Titik tengah poligon lahan
        $this->assertEqualsWithDelta(-7.5, (float) $laporan->latitude, 0.0001);
        $this->assertEqualsWithDelta(110.5, (float) $laporan->longitude, 0.0001);

        // Jenis tanaman di lahan ikut dikirim sebagai petunjuk ke Gemini
        Http::assertSent(fn (Request $request) => str_contains($request['contents'][0]['parts'][1]['text'], 'Cabai'));
    }

    public function test_petani_tidak_bisa_menautkan_lahan_milik_petani_lain(): void
    {
        Http::fake([self::URL_GEMINI => Http::response($this->responsGemini())]);
        ['user' => $petani] = $this->buatPetani();
        ['lahan' => $lahanOrangLain] = $this->buatPetani();

        $this->actingAs($petani)
            ->postJson(route('petani.deteksi-penyakit.store'), [
                'foto_tanaman' => UploadedFile::fake()->image('daun.jpg', 800, 600),
                'lahan_id'     => $lahanOrangLain->id,
            ])
            ->assertCreated();

        $laporan = DiseaseReport::sole();
        $this->assertNull($laporan->lahan_id);
        // Jatuh ke koordinat wilayah petani
        $this->assertEqualsWithDelta(-7.55, (float) $laporan->latitude, 0.0001);
    }

    public function test_role_lain_tidak_bisa_memakai_scanner_petani(): void
    {
        Http::fake();

        $this->actingAs($this->buatUser('user'))
            ->postJson(route('petani.deteksi-penyakit.store'), [
                'foto_tanaman' => UploadedFile::fake()->image('daun.jpg', 640, 480),
            ])
            ->assertForbidden();

        Http::assertNothingSent();
    }

    private function responsGemini(array $diagnosis = []): array
    {
        $diagnosis = $diagnosis ?: [
            'is_tanaman'        => true,
            'nama_tanaman'      => 'Cabai',
            'gejala'            => 'Bercak cekung kehitaman dengan lingkaran konsentris pada buah.',
            'nama_penyakit'     => 'Antraknosa (Colletotrichum capsici)',
            'penyebab'          => 'Jamur Colletotrichum yang berkembang saat hujan dan lembap.',
            'tingkat_akurasi'   => 87.5,
            'tingkat_risiko'    => 'tinggi',
            'solusi_penanganan' => "1. Petik dan musnahkan buah yang terinfeksi.\n2. Perbaiki jarak tanam.\n3. Semprot fungisida berbahan aktif propineb sesuai dosis label dan pakai APD.",
        ];

        return [
            'candidates' => [[
                'content'      => ['role' => 'model', 'parts' => [['text' => json_encode($diagnosis)]]],
                'finishReason' => 'STOP',
            ]],
            'modelVersion' => 'gemini-3.5-flash',
        ];
    }

    private function buatUser(string $role): User
    {
        return User::create([
            'username' => $role.'-'.Str::lower(Str::random(6)),
            'email'    => Str::lower(Str::random(10)).'@contoh.test',
            'password' => 'rahasia123',
            'role'     => $role,
        ]);
    }

    /**
     * @return array{user: User, profil: PetaniProfile, lahan: Lahan}
     */
    private function buatPetani(): array
    {
        $user = $this->buatUser('petani');
        $wilayah = Wilayah::create(['nama_wilayah' => 'Sukoharjo', 'latitude' => -7.55, 'longitude' => 110.8]);
        $profil = PetaniProfile::create(['user_id' => $user->id, 'wilayah_id' => $wilayah->id, 'luas_lahan' => 1.5]);
        $lahan = Lahan::create([
            'petani_profile_id' => $profil->id,
            'nama_lahan'        => 'Sawah Timur',
            'luas_ha'           => 0.75,
            'tanaman_aktif'     => 'Cabai',
            'koordinat_poligon' => [[-7.4, 110.4], [-7.6, 110.4], [-7.5, 110.7]],
        ]);

        return compact('user', 'profil', 'lahan');
    }
}
