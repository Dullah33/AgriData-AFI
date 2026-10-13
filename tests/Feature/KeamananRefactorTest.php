<?php

namespace Tests\Feature;

use App\Models\DiseaseReport;
use App\Models\ExtensionOfficer;
use App\Models\KeranjangItem;
use App\Models\Lahan;
use App\Models\MonthlyReport;
use App\Models\PetaniProfile;
use App\Models\Plant;
use App\Models\ProdukPanen;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\Ekspor\EksporCsv;
use App\Services\LocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

/**
 * Regresi untuk hasil code review: celah keamanan, N+1, dan bug yang
 * ditemukan saat refactor controller ke Service/Action/FormRequest.
 */
class KeamananRefactorTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Autentikasi
    // ---------------------------------------------------------------

    public function test_api_register_mengabaikan_role_dari_request(): void
    {
        $this->postJson('/api/register', [
            'username' => 'penyusup', 'email' => 'penyusup@contoh.test',
            'password' => 'rahasia123', 'role' => 'admin',
        ])->assertCreated()
            ->assertJsonPath('user.role', 'user')
            ->assertJsonMissingPath('user.password');

        $this->assertSame('user', User::firstWhere('email', 'penyusup@contoh.test')->role);
    }

    public function test_api_register_menolak_username_kembar(): void
    {
        $this->buatUser('user', 'budi');

        $this->postJson('/api/register', ['username' => 'budi', 'email' => 'lain@contoh.test', 'password' => 'rahasia123'])
            ->assertStatus(422)->assertJsonValidationErrors('username');
    }

    public function test_register_web_membuat_akun_user_dengan_username_dari_email(): void
    {
        $this->buatUser('user', 'sari');

        $this->post('/register', [
            'name' => 'Sari Lestari', 'email' => 'sari@contoh.test',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('login'));

        $user = User::firstWhere('email', 'sari@contoh.test');
        $this->assertSame(['sari1', 'user'], [$user->username, $user->role]);
        $this->assertTrue(password_verify('rahasia123', $user->password));
    }

    public function test_login_web_dibatasi_lima_percobaan_per_menit(): void
    {
        $this->buatUser('user', 'budi', 'budi@contoh.test');

        foreach (range(1, 5) as $i) {
            $this->post('/login', ['email' => 'budi@contoh.test', 'password' => 'salah'])->assertSessionHasErrors('email');
        }

        $this->post('/login', ['email' => 'budi@contoh.test', 'password' => 'rahasia123'])
            ->assertSessionHasErrors(['email' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.']);
        $this->assertGuest();
    }

    public function test_login_api_dibatasi_dan_membalas_429(): void
    {
        foreach (range(1, 5) as $i) {
            $this->postJson('/api/login', ['email' => 'x@contoh.test', 'password' => 'salah'])->assertStatus(401);
        }

        $this->postJson('/api/login', ['email' => 'x@contoh.test', 'password' => 'salah'])->assertStatus(429);
    }

    // ---------------------------------------------------------------
    // API produk panen
    // ---------------------------------------------------------------

    public function test_api_produk_hanya_petani_yang_boleh_menambah(): void
    {
        Sanctum::actingAs($this->buatUser('user'));
        $this->postJson('/api/produk-panen', $this->dataProduk())->assertForbidden();

        $petani = $this->buatUser('petani');
        Sanctum::actingAs($petani);
        $this->postJson('/api/produk-panen', $this->dataProduk())->assertCreated();

        $this->assertDatabaseHas('produk_panen', ['petani_id' => $petani->id, 'status' => 'tersedia']);
    }

    public function test_api_produk_tidak_bisa_diubah_atau_dihapus_orang_lain(): void
    {
        $produk = $this->buatProduk($this->buatUser('petani'));
        Sanctum::actingAs($this->buatUser('petani'));

        $this->putJson("/api/produk-panen/{$produk->id}", $this->dataProduk(['harga' => 1]))->assertForbidden();
        $this->deleteJson("/api/produk-panen/{$produk->id}")->assertForbidden();

        $this->assertEquals(42000, $produk->fresh()->harga);
        $this->assertSame('tersedia', $produk->fresh()->status);
    }

    public function test_api_hapus_produk_memakai_hapus_lunak(): void
    {
        $petani = $this->buatUser('petani');
        $produk = $this->buatProduk($petani);
        Sanctum::actingAs($petani);

        $this->deleteJson("/api/produk-panen/{$produk->id}")->assertOk();

        $this->assertSame('dihapus', $produk->fresh()->status);
        $this->getJson("/api/produk-panen/{$produk->id}")->assertNotFound();
        $this->getJson('/api/produk-panen')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/produk-panen/9999')->assertNotFound()
            ->assertJsonPath('message', 'Produk panen dengan ID 9999 tidak ada di sistem.');
    }

    public function test_api_daftar_produk_memvalidasi_filter_tanggal(): void
    {
        Sanctum::actingAs($this->buatUser('user'));

        $this->getJson('/api/produk-panen?start_date=bukan-tanggal&end_date=2026-01-01')
            ->assertStatus(422)->assertJsonValidationErrors('start_date');
    }

    // ---------------------------------------------------------------
    // Ekspor CSV
    // ---------------------------------------------------------------

    public function test_ekspor_csv_menetralkan_rumus_excel(): void
    {
        $csv = new EksporCsv;

        $this->assertSame("'=HYPERLINK(\"http://jahat\")", $csv->amankan('=HYPERLINK("http://jahat")'));
        $this->assertSame("'+cmd|calc", $csv->amankan('+cmd|calc'));
        $this->assertSame('+62812', $csv->amankan('+62812'), 'angka bertanda plus tidak dianggap rumus berbahaya');
        $this->assertSame("'@SUM(A1)", $csv->amankan('@SUM(A1)'));
        $this->assertSame('-12.50', $csv->amankan('-12.50'), 'angka negatif tetap angka');
        $this->assertSame('Kelompok Tani Makmur', $csv->amankan('Kelompok Tani Makmur'));
        $this->assertSame(15, $csv->amankan(15));
    }

    public function test_ekspor_petani_berisi_data_yang_sudah_diamankan(): void
    {
        [, $profil] = $this->buatPetani();
        $profil->update(['nama_kelompok_tani' => '=cmd|calc']);

        $isi = $this->actingAs($this->buatUser('admin'))->get(route('admin.laporan.petani.export'))
            ->assertOk()->streamedContent();

        $this->assertStringContainsString("'=cmd|calc", $isi);
        $this->assertStringContainsString('Kelompok Tani', $isi);
    }

    // ---------------------------------------------------------------
    // Penyuluh
    // ---------------------------------------------------------------

    public function test_penyuluh_hanya_bisa_menjadwalkan_petani_binaan(): void
    {
        [$penyuluh] = $this->buatPenyuluh('Sukoharjo');
        [, $binaan] = $this->buatPetani('Sukoharjo');
        [, $luar] = $this->buatPetani('Wonogiri');

        $this->actingAs($penyuluh)->post(route('penyuluh.kunjungan.store'), [
            'petani_profile_id' => $luar->id, 'tanggal_kunjungan' => now()->addDay()->toDateString(),
        ])->assertSessionHasErrors(['petani_profile_id' => 'Petani ini tidak berada di wilayah binaan Anda.']);

        $this->actingAs($penyuluh)->post(route('penyuluh.kunjungan.store'), [
            'petani_profile_id' => $binaan->id, 'tanggal_kunjungan' => now()->addDay()->toDateString(),
        ])->assertRedirect(route('penyuluh.kunjungan.index'));

        $this->assertDatabaseCount('field_visits', 1);
    }

    public function test_daftar_petani_binaan_tanpa_n_plus_1(): void
    {
        [$penyuluh] = $this->buatPenyuluh('Sukoharjo');
        foreach (range(1, 4) as $i) {
            $this->buatPetani('Sukoharjo');
        }

        // preventLazyLoading aktif di test: N+1 akan melempar exception
        $this->actingAs($penyuluh)->get(route('penyuluh.wilayah-binaan.index'))->assertOk()->assertSee('4 petani terdaftar');
        $this->actingAs($penyuluh)->get(route('penyuluh.kunjungan.create'))->assertOk();
    }

    public function test_laporan_bulanan_ganda_ditolak_dengan_pesan_bukan_error_500(): void
    {
        [$penyuluh, $officer] = $this->buatPenyuluh('Sukoharjo');
        MonthlyReport::create([
            'extension_officer_id' => $officer->id, 'bulan' => 10, 'tahun' => 2026,
            'ringkasan_kegiatan' => 'Kunjungan rutin', 'status' => 'draft',
        ]);

        $this->actingAs($penyuluh)->post(route('penyuluh.laporan-bulanan.store'), [
            'bulan' => 10, 'tahun' => 2026, 'ringkasan_kegiatan' => 'Lagi',
        ])->assertSessionHasErrors('bulan');

        $this->actingAs($penyuluh)->post(route('penyuluh.laporan-bulanan.store'), [
            'bulan' => 11, 'tahun' => 2026, 'ringkasan_kegiatan' => 'Bulan berikutnya',
        ])->assertRedirect(route('penyuluh.laporan-bulanan.index'));
    }

    public function test_admin_membuat_dan_menghapus_akun_penyuluh(): void
    {
        $admin = $this->buatUser('admin');

        $this->actingAs($admin)->post(route('admin.penyuluh.store'), [
            'username' => 'bu_wulan', 'email' => 'wulan@contoh.test', 'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123', 'wilayah_binaan' => 'Sukoharjo',
        ])->assertRedirect(route('admin.penyuluh.index'));

        $officer = ExtensionOfficer::with('user')->firstOrFail();
        $this->assertSame(['penyuluh', $admin->id], [$officer->user->role, $officer->assigned_by]);

        $this->actingAs($admin)->delete(route('admin.penyuluh.destroy', $officer));
        $this->assertDatabaseCount('extension_officers', 0);
        $this->assertDatabaseMissing('users', ['email' => 'wulan@contoh.test']);
    }

    // ---------------------------------------------------------------
    // Peta lahan & dashboard
    // ---------------------------------------------------------------

    public function test_peta_lahan_berjalan_di_sqlite_dan_escape_data_petani(): void
    {
        [, $profil] = $this->buatPetani('Sukoharjo');
        $lahan = Lahan::create([
            'petani_profile_id' => $profil->id, 'nama_lahan' => '<img src=x onerror=alert(1)>', 'luas_ha' => 1,
            'koordinat_poligon' => [[-7.60, 110.80], [-7.70, 110.80], [-7.70, 110.90]],
        ]);
        foreach (range(1, 5) as $i) {
            DiseaseReport::create([
                'user_id' => $profil->user_id, 'wilayah_id' => $profil->wilayah_id, 'lahan_id' => $lahan->id,
                'foto_tanaman' => 'x.jpg', 'nama_penyakit' => 'Antraknosa', 'confidence_score' => 80,
                'tingkat_risiko' => 'tinggi', 'status' => 'baru', 'latitude' => -7.65, 'longitude' => 110.85,
            ]);
        }

        $respons = $this->actingAs($this->buatUser('admin'))->get(route('admin.peta-lahan.index'))->assertOk();
        $html = $respons->getContent();

        $this->assertStringNotContainsString('<img src=x', $html, 'nama lahan tidak boleh muncul sebagai HTML mentah');
        $this->assertStringContainsString('function esc(', $html);
        $this->assertSame(5, $respons->viewData('trenChart')[now()->month - 1]);
        $this->assertSame([['wilayah' => 'Sukoharjo', 'jumlah' => 5]], $respons->viewData('wilayahRentan')->all());
    }

    public function test_filter_peta_lahan_mengabaikan_input_aneh(): void
    {
        $this->actingAs($this->buatUser('admin'))
            ->get(route('admin.peta-lahan.index', ['tahun' => 'abc', 'tingkat_risiko' => ['x'], 'nama_penyakit' => ['y']]))
            ->assertOk()
            ->assertViewHas('tahun', now()->year);
    }

    public function test_statistik_transaksi_bulan_ini_tidak_menghitung_tahun_lalu(): void
    {
        $pembeli = $this->buatUser('user');
        $produk = $this->buatProduk($this->buatUser('petani'));
        foreach ([now(), now()->subYear()] as $waktu) {
            $t = Transaksi::create(['user_id' => $pembeli->id, 'produk_id' => $produk->id, 'jumlah' => 1, 'total_harga' => 42000, 'status_transaksi' => 'selesai']);
            $t->forceFill(['created_at' => $waktu])->save();
        }

        $this->actingAs($this->buatUser('admin'))->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn ($stats) => $stats['transaksi_bulan_ini'] === 1);
    }

    public function test_badge_keranjang_dihitung_sekali_per_halaman(): void
    {
        $pembeli = $this->buatUser('user');
        KeranjangItem::create(['user_id' => $pembeli->id, 'produk_id' => $this->buatProduk($this->buatUser('petani'))->id, 'jumlah' => 2]);

        $query = 0;
        DB::listen(function ($q) use (&$query) {
            if (str_contains($q->sql, 'from "keranjang"') && str_contains($q->sql, 'count(*)')) {
                $query++;
            }
        });

        $this->actingAs($pembeli)->get(route('user.marketplace'))->assertOk()->assertSee('Keranjang (1 produk)');
        $this->assertSame(1, $query);
    }

    // ---------------------------------------------------------------
    // Validasi data
    // ---------------------------------------------------------------

    public function test_tanaman_menolak_nama_kembar_dan_gambar_berbahaya(): void
    {
        $admin = $this->buatUser('admin');
        Plant::create($this->dataTanaman(['kode' => 'CBR', 'nama' => 'Cabai']));

        $this->actingAs($admin)->post(route('admin.plants.store'), $this->dataTanaman([
            'kode' => 'CB2', 'nama' => 'Cabai', 'gambar' => 'x.jpg" onerror="alert(1)',
        ]))->assertSessionHasErrors(['nama', 'gambar']);

        $this->actingAs($admin)->post(route('admin.plants.store'), $this->dataTanaman([
            'kode' => 'TMT', 'nama' => 'Tomat', 'gambar' => 'assets/images/tanaman/Tomat.jpg',
            'langkah_budidaya' => [['title' => 'Semai', 'content' => 'Rendam benih'], ['title' => '', 'content' => '']],
            'tips_budidaya' => [['icon' => '', 'title' => 'Mulsa', 'content' => 'Pasang mulsa plastik']],
        ]))->assertRedirect(route('admin.plants.index'));

        $tomat = Plant::firstWhere('kode', 'TMT');
        $this->assertSame([['title' => 'Semai', 'content' => 'Rendam benih']], $tomat->langkah_budidaya);
        $this->assertSame('💡', $tomat->tips_budidaya[0]['icon']);
    }

    public function test_halaman_detail_tanaman_yang_tidak_ada_tidak_lagi_error_500(): void
    {
        $plant = Plant::create($this->dataTanaman());

        $this->actingAs($this->buatUser('admin'))->get("/admin/plants/{$plant->id}")->assertStatus(405);
    }

    public function test_poligon_lahan_rusak_diabaikan_dan_poligon_lama_dipertahankan(): void
    {
        [$petani, $profil] = $this->buatPetani();
        $form = ['nama_lahan' => 'Sawah A', 'luas_ha' => 1];

        $this->actingAs($petani)->post(route('petani.lahan.store'), $form + ['koordinat_poligon' => '[["a","b"],[1,2],[3,4]]'])
            ->assertRedirect(route('petani.lahan.index'));
        $lahan = Lahan::firstOrFail();
        $this->assertNull($lahan->koordinat_poligon);

        $valid = [[-7.60, 110.80], [-7.70, 110.80], [-7.70, 110.90]];
        $this->actingAs($petani)->put(route('petani.lahan.update', $lahan), $form + ['status' => 'aktif', 'koordinat_poligon' => json_encode($valid)]);
        $this->assertSame($valid, $lahan->fresh()->koordinat_poligon);

        $this->actingAs($petani)->put(route('petani.lahan.update', $lahan), $form + ['status' => 'aktif', 'koordinat_poligon' => '[[999,999],[1,2],[3,4]]']);
        $this->assertSame($valid, $lahan->fresh()->koordinat_poligon);

        // Lahan petani lain tidak bisa diubah
        [$lain] = $this->buatPetani();
        $this->actingAs($lain)->put(route('petani.lahan.update', $lahan), $form + ['status' => 'aktif'])->assertForbidden();
    }

    public function test_ulasan_hanya_untuk_transaksi_selesai_milik_sendiri(): void
    {
        $pembeli = $this->buatUser('user');
        $produk = $this->buatProduk($this->buatUser('petani'));
        $diproses = Transaksi::create(['user_id' => $pembeli->id, 'produk_id' => $produk->id, 'jumlah' => 1, 'total_harga' => 42000, 'status_transaksi' => 'diproses']);
        $selesai = Transaksi::create(['user_id' => $pembeli->id, 'produk_id' => $produk->id, 'jumlah' => 1, 'total_harga' => 42000, 'status_transaksi' => 'selesai']);

        $this->actingAs($pembeli)->get(route('user.ulasan.create', $diproses))->assertForbidden();
        $this->actingAs($this->buatUser('user'))->get(route('user.ulasan.create', $selesai))->assertForbidden();

        $this->actingAs($pembeli)->post(route('user.ulasan.store', $selesai), ['rating' => 5, 'komentar' => 'Segar'])
            ->assertRedirect(route('user.riwayat-ulasan'));
        $this->actingAs($pembeli)->post(route('user.ulasan.store', $selesai), ['rating' => 4])->assertForbidden();
        $this->assertDatabaseCount('ulasan_rating', 1);
    }

    // ---------------------------------------------------------------
    // Halaman cuaca
    // ---------------------------------------------------------------

    public function test_api_cuaca_tidak_membocorkan_pesan_error_internal(): void
    {
        $this->mock(LocationService::class)->shouldReceive('geocode')
            ->andThrow(new RuntimeException('SQLSTATE[HY000]: rahasia di server'));

        $this->actingAs($this->buatUser('user'))->getJson('/api/cuaca/recommendations/Jawa%20Tengah/Sukoharjo/Baki')
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonMissing(['error' => 'SQLSTATE[HY000]: rahasia di server'])
            ->assertJsonPath('error', 'Terjadi kesalahan pada server. Silakan coba lagi.');
    }

    public function test_daftar_wilayah_indonesia_di_cache_dan_kode_harus_angka(): void
    {
        Http::fake(['emsifa.github.io/*' => Http::response([['id' => '33', 'name' => 'JAWA TENGAH']])]);
        $user = $this->buatUser('user');

        $this->actingAs($user)->getJson('/api/provinces')->assertOk()->assertJsonPath('0.name', 'JAWA TENGAH');
        $this->actingAs($user)->getJson('/api/provinces')->assertOk();
        Http::assertSentCount(1);

        $this->actingAs($user)->getJson('/api/regencies/..%2F..%2Fsecret')->assertNotFound();
    }

    // ---------------------------------------------------------------

    private function buatUser(string $role, ?string $username = null, ?string $email = null): User
    {
        return User::create([
            'username' => $username ?? $role.'-'.Str::lower(Str::random(6)),
            'email'    => $email ?? Str::lower(Str::random(10)).'@contoh.test',
            'password' => 'rahasia123',
            'role'     => $role,
        ]);
    }

    private function buatPetani(string $wilayah = 'Sukoharjo'): array
    {
        $w = Wilayah::firstOrCreate(['nama_wilayah' => $wilayah], ['latitude' => -7.68, 'longitude' => 110.84]);
        $petani = $this->buatUser('petani');
        $profil = PetaniProfile::create(['user_id' => $petani->id, 'wilayah_id' => $w->id, 'luas_lahan' => 1]);

        return [$petani, $profil];
    }

    private function buatPenyuluh(string $wilayah): array
    {
        $user = $this->buatUser('penyuluh');
        $officer = ExtensionOfficer::create(['user_id' => $user->id, 'wilayah_binaan' => $wilayah, 'status' => 'aktif']);

        return [$user, $officer];
    }

    private function buatProduk(User $petani): ProdukPanen
    {
        return ProdukPanen::create($this->dataProduk() + ['petani_id' => $petani->id, 'status' => 'tersedia']);
    }

    private function dataProduk(array $timpa = []): array
    {
        return array_replace([
            'nama_produk' => 'Cabai rawit', 'kategori' => 'Sayuran', 'deskripsi' => 'Segar',
            'harga' => 42000, 'satuan' => 'kg', 'stok' => 10,
        ], $timpa);
    }

    private function dataTanaman(array $timpa = []): array
    {
        return array_replace([
            'kode' => 'PDI', 'nama' => 'Padi', 'suhu_ideal' => '24-29°C', 'min_suhu' => 24, 'max_suhu' => 29,
            'kelembapan_ideal' => '70-90%', 'min_kelembaban' => 70, 'max_kelembaban' => 90,
            'curah_hujan_ideal' => '1500-2000 mm', 'musim' => 'Hujan', 'jenis_tanah' => 'Aluvial',
        ], $timpa);
    }
}
