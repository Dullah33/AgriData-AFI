<?php

namespace Tests\Feature;

use App\Exceptions\PlantNotDetectedException;
use App\Services\GeminiEnricherService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class GeminiEnricherServiceTest extends TestCase
{
    // Objek UploadedFile disimpan supaya file sementaranya tidak ikut terhapus
    private UploadedFile $upload;

    private string $foto;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.gemini.api_key' => 'kunci-test', 'services.gemini.model' => 'gemini-3.5-flash']);
        Sleep::fake();

        $this->upload = UploadedFile::fake()->image('daun.jpg', 400, 300);
        $this->foto = $this->upload->getRealPath();
    }

    public function test_respons_dibungkus_markdown_dan_skala_0_sampai_1_tetap_terbaca(): void
    {
        $this->fakeTeks("```json\n".json_encode([
            'is_tanaman'        => true,
            'nama_tanaman'      => 'Padi',
            'gejala'            => 'Bercak belah ketupat pada daun.',
            'nama_penyakit'     => str_repeat('Blas ', 40),
            'penyebab'          => 'Jamur Pyricularia oryzae.',
            'tingkat_akurasi'   => 0.92,
            'tingkat_risiko'    => 'SANGAT TINGGI',
            'solusi_penanganan' => ['1. Pakai varietas tahan.', '2. Kurangi pupuk N berlebih.'],
        ])."\n```");

        $hasil = app(GeminiEnricherService::class)->analisisGambar($this->foto);

        $this->assertSame(92.0, $hasil['confidence_score']);
        $this->assertSame('sedang', $hasil['tingkat_risiko']);
        $this->assertLessThanOrEqual(150, mb_strlen($hasil['nama_penyakit']));
        $this->assertSame("1. Pakai varietas tahan.\n2. Kurangi pupuk N berlebih.", $hasil['penanganan']);
    }

    public function test_foto_bukan_tanaman_melempar_exception_khusus(): void
    {
        $this->fakeTeks(json_encode(['is_tanaman' => false, 'nama_penyakit' => '-']));

        $this->expectException(PlantNotDetectedException::class);

        app(GeminiEnricherService::class)->analisisGambar($this->foto);
    }

    public function test_permintaan_yang_diblokir_dianggap_gagal(): void
    {
        Http::fake(['*' => Http::response(['promptFeedback' => ['blockReason' => 'SAFETY']])]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('SAFETY');

        app(GeminiEnricherService::class)->analisisGambar($this->foto);
    }

    public function test_jawaban_terpotong_dianggap_gagal(): void
    {
        $this->fakeTeks('{"is_tanaman": true, "nama_penyakit": "Hawar');

        $this->expectException(\RuntimeException::class);

        app(GeminiEnricherService::class)->analisisGambar($this->foto);
    }

    public function test_lengkapi_mengisi_penyebab_dan_penanganan(): void
    {
        $this->fakeTeks(json_encode(['penyebab' => 'Jamur Diplocarpon rosae.', 'penanganan' => 'Buang daun yang sakit.']));

        $hasil = app(GeminiEnricherService::class)->lengkapi('Bercak Hitam', 'Bercak hitam pada daun mawar');

        $this->assertSame(['penyebab' => 'Jamur Diplocarpon rosae.', 'penanganan' => 'Buang daun yang sakit.'], $hasil);
    }

    public function test_lengkapi_dilewati_kalau_api_key_belum_diatur(): void
    {
        config(['services.gemini.api_key' => null]);
        Http::fake();

        $this->assertNull(app(GeminiEnricherService::class)->lengkapi('Bercak Hitam', '-'));
        Http::assertNothingSent();
    }

    private function fakeTeks(string $teks): void
    {
        Http::fake(['*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => $teks]]], 'finishReason' => 'STOP']],
        ])]);
    }
}
