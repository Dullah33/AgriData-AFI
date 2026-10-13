<?php

namespace Tests\Feature;

use App\Models\DiseaseReport;
use App\Models\ExtensionOfficer;
use App\Models\FieldVisit;
use App\Models\PetaniProfile;
use App\Models\ProdukPanen;
use App\Models\Transaksi;
use App\Models\UlasanRating;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Memastikan halaman hasil redesign (landing, login/daftar, dashboard tiap
 * role, sidebar) bisa dirender, baik saat data kosong maupun berisi.
 */
class TampilanDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_untuk_tamu_menampilkan_tombol_daftar(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Tanaman sehat,', false)
            ->assertSee('Daftar gratis')
            ->assertSee('id="features"', false)
            ->assertSee('id="about"', false)
            ->assertSee('id="contact"', false)
            ->assertDontSee('—');
    }

    public function test_landing_page_untuk_pengguna_login_mengarah_ke_dashboard(): void
    {
        $this->actingAs($this->buatUser('user'))
            ->get('/')
            ->assertOk()
            ->assertSee('Buka dashboard')
            ->assertDontSee('Daftar gratis');
    }

    public function test_halaman_login_dan_daftar_bisa_dibuka(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('AgriScan');
        $this->get(route('register'))->assertOk()->assertSee('AgriScan');
    }

    public function test_dashboard_kosong_menampilkan_empty_state(): void
    {
        $this->actingAs($this->buatUser('admin'))->get(route('dashboard'))
            ->assertOk()->assertSee('Semua petani sudah terverifikasi')->assertSee('Belum ada ulasan');

        $this->actingAs($this->buatUser('petani'))->get(route('dashboard'))
            ->assertOk()->assertSee('Belum ada pesanan baru')->assertSee('Belum pernah scan');

        $this->actingAs($this->buatUser('penyuluh'))->get(route('dashboard'))
            ->assertOk()->assertSee('Akun Anda belum punya wilayah binaan');

        $this->actingAs($this->buatUser('user'))->get(route('dashboard'))
            ->assertOk()->assertSee('Belum ada pesanan')->assertSee('Belum ada produk tersedia');
    }

    public function test_dashboard_berisi_data_menampilkan_daftar_dan_menu_aktif(): void
    {
        $wilayah = Wilayah::create(['nama_wilayah' => 'Sukoharjo', 'latitude' => -7.68, 'longitude' => 110.84]);
        $petani = $this->buatUser('petani', 'pak_slamet');
        $profil = PetaniProfile::create([
            'user_id' => $petani->id, 'wilayah_id' => $wilayah->id, 'luas_lahan' => 1.2, 'komoditas_utama' => 'Cabai rawit',
        ]);
        $pembeli = $this->buatUser('user', 'bu_ratna');

        $produk = ProdukPanen::create([
            'petani_id' => $petani->id, 'nama_produk' => 'Cabai rawit merah', 'deskripsi' => 'Panen pagi',
            'harga' => 42000, 'satuan' => 'kg', 'stok' => 30, 'status' => 'tersedia',
        ]);
        $pesanan = Transaksi::create([
            'user_id' => $pembeli->id, 'produk_id' => $produk->id, 'jumlah' => 3,
            'total_harga' => 126000, 'status_transaksi' => 'pending',
        ]);
        $selesai = Transaksi::create([
            'user_id' => $pembeli->id, 'produk_id' => $produk->id, 'jumlah' => 1,
            'total_harga' => 42000, 'status_transaksi' => 'selesai',
        ]);
        UlasanRating::create(['transaksi_id' => $selesai->id, 'rating' => 4, 'komentar' => 'Cabainya segar.', 'status' => 'aktif']);

        DiseaseReport::create([
            'user_id' => $petani->id, 'wilayah_id' => $wilayah->id, 'foto_tanaman' => 'deteksi-penyakit/contoh.jpg',
            'nama_penyakit' => 'Antraknosa (Colletotrichum capsici)', 'confidence_score' => 88,
            'tingkat_risiko' => 'tinggi', 'status' => 'baru',
        ]);

        $penyuluh = $this->buatUser('penyuluh', 'bu_wulan');
        $officer = ExtensionOfficer::create(['user_id' => $penyuluh->id, 'wilayah_binaan' => 'Sukoharjo', 'status' => 'aktif']);
        FieldVisit::create([
            'extension_officer_id' => $officer->id, 'petani_profile_id' => $profil->id,
            'tanggal_kunjungan' => now()->addDays(2)->toDateString(), 'status' => 'terjadwal',
        ]);

        $this->actingAs($this->buatUser('admin'))->get(route('dashboard'))
            ->assertOk()->assertSee('pak_slamet')->assertSee('Cabai rawit')->assertSee('Cabainya segar.')
            ->assertSee('aria-current="page"', false);

        $this->actingAs($petani)->get(route('dashboard'))
            ->assertOk()->assertSee('Cabai rawit merah')->assertSee('Menunggu')->assertSee('Antraknosa');

        $this->actingAs($penyuluh)->get(route('dashboard'))
            ->assertOk()->assertSee('Wilayah binaan: Sukoharjo')->assertSee('pak_slamet')->assertSee('Terjadwal');

        $this->actingAs($pembeli)->get(route('dashboard'))
            ->assertOk()->assertSee('Cabai rawit merah')->assertSee('Rp 42.000');
    }

    public function test_menu_sidebar_ikut_aktif_di_halaman_turunan(): void
    {
        $petani = $this->buatUser('petani');
        $laporan = DiseaseReport::create([
            'user_id' => $petani->id, 'foto_tanaman' => 'deteksi-penyakit/contoh.jpg',
            'nama_penyakit' => 'Bercak Daun', 'confidence_score' => 70, 'tingkat_risiko' => 'sedang', 'status' => 'baru',
        ]);

        $html = $this->actingAs($petani)->get(route('petani.deteksi-penyakit.show', $laporan))->assertOk()->getContent();

        // Hanya menu "Riwayat Deteksi Penyakit" yang menyala, bukan "AI Scanner Penyakit"
        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        $this->assertMatchesRegularExpression('/aria-current="page"[^>]*>.*?Riwayat Deteksi Penyakit/s', $html);
    }

    private function buatUser(string $role, ?string $username = null): User
    {
        return User::create([
            'username' => $username ?? $role.'-'.Str::lower(Str::random(6)),
            'email'    => Str::lower(Str::random(10)).'@contoh.test',
            'password' => 'rahasia123',
            'role'     => $role,
        ]);
    }
}
