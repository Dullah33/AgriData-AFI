<?php

namespace Tests\Feature;

use App\Exceptions\StokTidakCukupException;
use App\Exceptions\TransisiStatusTidakValidException;
use App\Models\KeranjangItem;
use App\Models\Pembayaran;
use App\Models\ProdukPanen;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\Marketplace\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarketplaceTransaksiTest extends TestCase
{
    use RefreshDatabase;

    private const SERVER_KEY = 'SB-Mid-server-uji';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'marketplace.rekening' => ['bank' => 'Bank BRI', 'nomor' => '0123456789', 'atas_nama' => 'Dinas Pertanian AgriScan'],
            'marketplace.batas_bayar_jam' => 24,
            'services.midtrans.server_key' => null,
            'services.midtrans.simulasi' => false,
        ]);

        Storage::fake('local');
    }

    // ------------------------------------------------------------------
    // Keranjang & validasi stok (Form Request)
    // ------------------------------------------------------------------

    public function test_tambah_ke_keranjang_menggabungkan_jumlah_produk_yang_sama(): void
    {
        $pembeli = $this->buatUser('user');
        $produk = $this->buatProduk(stok: 10);

        $this->actingAs($pembeli)->post(route('user.keranjang.store', $produk), ['jumlah' => 2])->assertSessionHasNoErrors();
        $this->actingAs($pembeli)->post(route('user.keranjang.store', $produk), ['jumlah' => 3])->assertSessionHasNoErrors();

        $this->assertSame(1, KeranjangItem::count());
        $this->assertSame(5, KeranjangItem::sole()->jumlah);
    }

    public function test_tidak_bisa_membeli_melebihi_stok(): void
    {
        $pembeli = $this->buatUser('user');
        $produk = $this->buatProduk(stok: 4);

        $this->actingAs($pembeli)->post(route('user.keranjang.store', $produk), ['jumlah' => 5])
            ->assertSessionHasErrors(['jumlah' => 'Jumlah melebihi stok yang tersedia (4).']);

        $this->assertSame(0, KeranjangItem::count());
    }

    public function test_jumlah_yang_sudah_di_keranjang_ikut_dihitung(): void
    {
        $pembeli = $this->buatUser('user');
        $produk = $this->buatProduk(stok: 5);
        KeranjangItem::create(['user_id' => $pembeli->id, 'produk_id' => $produk->id, 'jumlah' => 4]);

        $this->actingAs($pembeli)->post(route('user.keranjang.store', $produk), ['jumlah' => 2])
            ->assertSessionHasErrors('jumlah');

        $this->assertSame(4, KeranjangItem::sole()->jumlah);
    }

    public function test_beli_sekarang_langsung_ke_checkout(): void
    {
        $produk = $this->buatProduk(stok: 5);

        $this->actingAs($this->buatUser('user'))
            ->post(route('user.keranjang.store', $produk), ['jumlah' => 1, 'aksi' => 'beli'])
            ->assertRedirect(route('user.checkout.create'));
    }

    public function test_ubah_jumlah_keranjang_divalidasi_stok_dan_kepemilikan(): void
    {
        $pembeli = $this->buatUser('user');
        $produk = $this->buatProduk(stok: 3);
        $item = KeranjangItem::create(['user_id' => $pembeli->id, 'produk_id' => $produk->id, 'jumlah' => 1]);

        $this->actingAs($pembeli)->patch(route('user.keranjang.update', $item), ['jumlah' => 9])->assertSessionHasErrors('jumlah');
        $this->actingAs($pembeli)->patch(route('user.keranjang.update', $item), ['jumlah' => 3])->assertSessionHasNoErrors();
        $this->assertSame(3, $item->fresh()->jumlah);

        $this->actingAs($this->buatUser('user'))->patch(route('user.keranjang.update', $item), ['jumlah' => 2])->assertForbidden();
        $this->actingAs($this->buatUser('user'))->delete(route('user.keranjang.destroy', $item))->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Checkout dengan DB::transaction
    // ------------------------------------------------------------------

    public function test_checkout_mengurangi_stok_dan_mencatat_transaksi(): void
    {
        $pembeli = $this->buatUser('user');
        $cabai = $this->buatProduk(stok: 10, harga: 42000);
        $tomat = $this->buatProduk(stok: 6, harga: 12500);
        $this->isiKeranjang($pembeli, [[$cabai, 3], [$tomat, 2]]);

        $respons = $this->actingAs($pembeli)->post(route('user.checkout.store'), $this->dataCheckout());

        $pembayaran = Pembayaran::sole();
        $respons->assertRedirect(route('user.pembayaran.show', $pembayaran));

        $this->assertSame(Pembayaran::MENUNGGU_PEMBAYARAN, $pembayaran->status);
        $this->assertSame('151000.00', $pembayaran->jumlah);
        $this->assertTrue($pembayaran->batas_bayar->between(now()->addHours(23), now()->addHours(25)));
        $this->assertStringStartsWith('INV-', $pembayaran->kode);

        $this->assertSame(7, $cabai->fresh()->stok);
        $this->assertSame(4, $tomat->fresh()->stok);
        $this->assertSame(0, KeranjangItem::count());

        $transaksi = Transaksi::where('produk_id', $cabai->id)->sole();
        $this->assertSame($pembayaran->id, $transaksi->pembayaran_id);
        $this->assertSame(Transaksi::MENUNGGU, $transaksi->status_transaksi);
        $this->assertSame('42000.00', $transaksi->harga_satuan);
        $this->assertSame('126000.00', $transaksi->total_harga);
        $this->assertSame('Jl. Veteran No. 12, Bendosari, Sukoharjo', $transaksi->alamat_pengiriman);

        // Harga produk berubah setelahnya tidak memengaruhi riwayat transaksi
        $cabai->update(['harga' => 60000]);
        $this->assertSame('42000.00', $transaksi->fresh()->harga_satuan);
    }

    public function test_checkout_ditolak_kalau_stok_berubah_sejak_masuk_keranjang(): void
    {
        $pembeli = $this->buatUser('user');
        $produk = $this->buatProduk(stok: 5);
        $this->isiKeranjang($pembeli, [[$produk, 5]]);
        $produk->update(['stok' => 3]);   // dibeli orang lain

        $this->actingAs($pembeli)->post(route('user.checkout.store'), $this->dataCheckout())
            ->assertSessionHasErrors('keranjang');

        $this->assertSame(0, Pembayaran::count());
        $this->assertSame(3, $produk->fresh()->stok);
    }

    public function test_checkout_di_rollback_kalau_stok_salah_satu_produk_habis_saat_dikunci(): void
    {
        $pembeli = $this->buatUser('user');
        $a = $this->buatProduk(stok: 10);
        $b = $this->buatProduk(stok: 5);
        $this->isiKeranjang($pembeli, [[$a, 2], [$b, 5]]);

        // Pembeli lain menghabiskan stok B tepat setelah validasi lolos
        $b->update(['stok' => 1]);

        try {
            app(CheckoutService::class)->checkout($pembeli, $this->dataCheckout());
            $this->fail('Checkout seharusnya gagal.');
        } catch (StokTidakCukupException $e) {
            $this->assertStringContainsString('tersisa 1', $e->getMessage());
        }

        // Stok A yang sempat dikurangi kembali utuh, tidak ada data yatim
        $this->assertSame(10, $a->fresh()->stok);
        $this->assertSame(1, $b->fresh()->stok);
        $this->assertSame(0, Pembayaran::count());
        $this->assertSame(0, Transaksi::count());
        $this->assertSame(2, KeranjangItem::count());
    }

    public function test_checkout_di_rollback_kalau_database_gagal_di_tengah_jalan(): void
    {
        $pembeli = $this->buatUser('user');
        $a = $this->buatProduk(stok: 10);
        $b = $this->buatProduk(stok: 10);
        $this->isiKeranjang($pembeli, [[$a, 4], [$b, 4]]);

        // Simulasikan error database saat mencatat transaksi kedua
        $ke = 0;
        Transaksi::creating(function () use (&$ke) {
            if (++$ke === 2) {
                throw new \RuntimeException('Koneksi database terputus');
            }
        });

        try {
            $this->withoutExceptionHandling()->actingAs($pembeli)->post(route('user.checkout.store'), $this->dataCheckout());
            $this->fail('Checkout seharusnya gagal.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Koneksi database terputus', $e->getMessage());
        }

        $this->assertSame(10, $a->fresh()->stok);
        $this->assertSame(10, $b->fresh()->stok);
        $this->assertSame(0, Pembayaran::count());
        $this->assertSame(0, Transaksi::count());
        $this->assertSame(2, KeranjangItem::count());
    }

    // ------------------------------------------------------------------
    // Transfer manual: unggah bukti & verifikasi admin
    // ------------------------------------------------------------------

    public function test_pembeli_mengunggah_bukti_transfer(): void
    {
        [$pembeli, $pembayaran] = $this->buatPesanan();

        $this->actingAs($this->buatUser('user'))
            ->post(route('user.pembayaran.bukti.store', $pembayaran), ['bukti_transfer' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertForbidden();

        $this->actingAs($pembeli)
            ->post(route('user.pembayaran.bukti.store', $pembayaran), ['bukti_transfer' => UploadedFile::fake()->create('bukti.txt', 5, 'text/plain')])
            ->assertSessionHasErrors('bukti_transfer');

        $this->actingAs($pembeli)
            ->post(route('user.pembayaran.bukti.store', $pembayaran), ['bukti_transfer' => UploadedFile::fake()->image('bukti.jpg', 600, 900)])
            ->assertSessionHasNoErrors();

        $pembayaran->refresh();
        $this->assertSame(Pembayaran::MENUNGGU_VERIFIKASI, $pembayaran->status);
        Storage::disk('local')->assertExists($pembayaran->bukti_transfer);

        // Bukti bisa dibuka pemiliknya, tidak oleh pembeli lain
        $this->actingAs($pembeli)->get(route('user.pembayaran.bukti', $pembayaran))->assertOk();
        $this->actingAs($this->buatUser('user'))->get(route('user.pembayaran.bukti', $pembayaran))->assertNotFound();
    }

    public function test_admin_memverifikasi_bukti_dan_pesanan_diteruskan_ke_petani(): void
    {
        [, $pembayaran] = $this->buatPesanan(denganBukti: true);
        $admin = $this->buatUser('admin');

        $this->actingAs($admin)->get(route('admin.pembayaran.show', $pembayaran))->assertOk()->assertSee($pembayaran->kode);
        $this->actingAs($admin)->post(route('admin.pembayaran.verifikasi', $pembayaran))->assertSessionHasNoErrors();

        $pembayaran->refresh();
        $this->assertSame(Pembayaran::LUNAS, $pembayaran->status);
        $this->assertSame($admin->id, $pembayaran->diverifikasi_oleh);
        $this->assertNotNull($pembayaran->dibayar_at);
        $this->assertTrue($pembayaran->transaksis->every(fn ($t) => $t->status_transaksi === Transaksi::DIPROSES));
    }

    public function test_admin_menolak_bukti_dan_pembeli_bisa_unggah_ulang(): void
    {
        [$pembeli, $pembayaran] = $this->buatPesanan(denganBukti: true);

        $this->actingAs($this->buatUser('admin'))
            ->post(route('admin.pembayaran.tolak', $pembayaran), ['alasan' => 'Nominal kurang Rp 5.000'])
            ->assertSessionHasNoErrors();

        $pembayaran->refresh();
        $this->assertSame(Pembayaran::MENUNGGU_PEMBAYARAN, $pembayaran->status);
        $this->assertSame('Nominal kurang Rp 5.000', $pembayaran->catatan_verifikasi);

        $this->actingAs($pembeli)
            ->post(route('user.pembayaran.bukti.store', $pembayaran), ['bukti_transfer' => UploadedFile::fake()->image('ulang.png')])
            ->assertSessionHasNoErrors();
        $this->assertSame(Pembayaran::MENUNGGU_VERIFIKASI, $pembayaran->fresh()->status);
    }

    public function test_bukan_admin_tidak_bisa_memverifikasi(): void
    {
        [$pembeli, $pembayaran] = $this->buatPesanan(denganBukti: true);

        $this->actingAs($pembeli)->post(route('admin.pembayaran.verifikasi', $pembayaran))->assertForbidden();
        $this->assertSame(Pembayaran::MENUNGGU_VERIFIKASI, $pembayaran->fresh()->status);
    }

    // ------------------------------------------------------------------
    // Alur status dari sisi petani
    // ------------------------------------------------------------------

    public function test_petani_hanya_bisa_menandai_selesai_setelah_pembayaran_lunas(): void
    {
        [, $pembayaran, $produk] = $this->buatPesanan();
        $transaksi = $pembayaran->transaksis->first();
        $petani = $produk->petani;

        $this->actingAs($petani)->post(route('petani.pesanan.selesai', $transaksi))
            ->assertSessionHas('error', 'Pesanan berstatus "Menunggu" tidak bisa diubah menjadi "Selesai".');
        $this->assertSame(Transaksi::MENUNGGU, $transaksi->fresh()->status_transaksi);

        $this->bayarLunas($pembayaran);

        $this->actingAs($this->buatUser('petani'))->post(route('petani.pesanan.selesai', $transaksi))->assertForbidden();
        $this->actingAs($petani)->post(route('petani.pesanan.selesai', $transaksi))->assertSessionHas('success');
        $this->assertSame(Transaksi::SELESAI, $transaksi->fresh()->status_transaksi);
        $this->assertNotNull($transaksi->fresh()->selesai_at);
    }

    public function test_petani_menolak_sebelum_dibayar_mengembalikan_stok_dan_menghitung_ulang_tagihan(): void
    {
        $pembeli = $this->buatUser('user');
        $a = $this->buatProduk(stok: 10, harga: 10000);
        $b = $this->buatProduk(stok: 10, harga: 5000);
        $this->isiKeranjang($pembeli, [[$a, 2], [$b, 3]]);
        $pembayaran = app(CheckoutService::class)->checkout($pembeli, $this->dataCheckout());
        $transaksiA = $pembayaran->transaksis()->where('produk_id', $a->id)->first();

        $this->actingAs($a->petani)->post(route('petani.pesanan.tolak', $transaksiA), ['alasan' => ''])->assertSessionHasErrors('alasan');
        $this->actingAs($a->petani)->post(route('petani.pesanan.tolak', $transaksiA), ['alasan' => 'Cabai rusak terkena hujan'])->assertSessionHas('success');

        $this->assertSame(Transaksi::BATAL, $transaksiA->fresh()->status_transaksi);
        $this->assertSame(10, $a->fresh()->stok);
        $this->assertSame('15000.00', $pembayaran->fresh()->jumlah);
        $this->assertSame(Pembayaran::MENUNGGU_PEMBAYARAN, $pembayaran->fresh()->status);
    }

    public function test_petani_tidak_bisa_menolak_setelah_pembeli_mengirim_bukti(): void
    {
        [, $pembayaran, $produk] = $this->buatPesanan(denganBukti: true);
        $transaksi = $pembayaran->transaksis->first();

        $this->actingAs($produk->petani)->post(route('petani.pesanan.tolak', $transaksi), ['alasan' => 'Stok tidak ada'])
            ->assertSessionHas('error');

        $this->assertSame(Transaksi::MENUNGGU, $transaksi->fresh()->status_transaksi);
        $this->assertSame(7, $produk->fresh()->stok);
    }

    // ------------------------------------------------------------------
    // Pembatalan & kedaluwarsa
    // ------------------------------------------------------------------

    public function test_pembeli_membatalkan_pesanan_dan_stok_kembali(): void
    {
        [$pembeli, $pembayaran, $produk] = $this->buatPesanan();

        $this->actingAs($pembeli)->post(route('user.pembayaran.batal', $pembayaran))->assertRedirect(route('user.pesanan'));

        $this->assertSame(Pembayaran::DIBATALKAN, $pembayaran->fresh()->status);
        $this->assertSame(10, $produk->fresh()->stok);
        $this->assertSame(Transaksi::BATAL, $pembayaran->transaksis()->first()->status_transaksi);
    }

    public function test_pesanan_yang_lewat_batas_bayar_dibatalkan_otomatis(): void
    {
        [, $belumBayar, $produkA] = $this->buatPesanan();
        [, $sudahKirimBukti, $produkB] = $this->buatPesanan(denganBukti: true);

        $this->travel(25)->hours();
        $this->artisan('pembayaran:kedaluwarsa')->expectsOutput('1 pembayaran ditandai kedaluwarsa.')->assertSuccessful();

        $this->assertSame(Pembayaran::KEDALUWARSA, $belumBayar->fresh()->status);
        $this->assertSame(10, $produkA->fresh()->stok);

        // Yang sudah mengirim bukti tetap menunggu verifikasi admin
        $this->assertSame(Pembayaran::MENUNGGU_VERIFIKASI, $sudahKirimBukti->fresh()->status);
        $this->assertSame(7, $produkB->fresh()->stok);
    }

    public function test_pembatalan_tidak_mengembalikan_stok_dua_kali(): void
    {
        [, $pembayaran, $produk] = $this->buatPesanan();
        $transaksi = $pembayaran->transaksis->first();

        $transaksi->batalkan('Pertama');

        $this->expectException(TransisiStatusTidakValidException::class);
        try {
            $transaksi->batalkan('Kedua');
        } finally {
            $this->assertSame(10, $produk->fresh()->stok);
        }
    }

    // ------------------------------------------------------------------
    // Midtrans
    // ------------------------------------------------------------------

    public function test_metode_midtrans_tidak_bisa_dipilih_kalau_belum_dikonfigurasi(): void
    {
        $pembeli = $this->buatUser('user');
        $this->isiKeranjang($pembeli, [[$this->buatProduk(stok: 5), 1]]);

        $this->actingAs($pembeli)->post(route('user.checkout.store'), $this->dataCheckout(Pembayaran::METODE_MIDTRANS))
            ->assertSessionHasErrors('metode');
    }

    public function test_halaman_pembayaran_membuat_snap_token_midtrans(): void
    {
        config(['services.midtrans.server_key' => self::SERVER_KEY, 'services.midtrans.client_key' => 'SB-Mid-client-uji']);
        Http::fake(['app.sandbox.midtrans.com/*' => Http::response(['token' => 'snap-token-uji', 'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/uji'], 201)]);
        [$pembeli, $pembayaran] = $this->buatPesanan(metode: Pembayaran::METODE_MIDTRANS);

        $this->actingAs($pembeli)->get(route('user.pembayaran.show', $pembayaran))
            ->assertOk()
            ->assertSee('snap-token-uji')
            ->assertSee('https://app.sandbox.midtrans.com/snap/snap.js', false);

        $pembayaran->refresh();
        $this->assertSame('snap-token-uji', $pembayaran->snap_token);
        $this->assertStringStartsWith($pembayaran->kode.'-', $pembayaran->gateway_order_id);

        Http::assertSent(fn (HttpRequest $r) => $r->hasHeader('Authorization', 'Basic '.base64_encode(self::SERVER_KEY.':'))
            && $r['transaction_details']['gross_amount'] === 126000
            && $r['transaction_details']['order_id'] === $pembayaran->gateway_order_id
            && $r['item_details'][0]['quantity'] === 3);
    }

    public function test_webhook_midtrans_settlement_melunasi_pembayaran_dan_idempoten(): void
    {
        config(['services.midtrans.server_key' => self::SERVER_KEY]);
        [, $pembayaran] = $this->buatPesanan(metode: Pembayaran::METODE_MIDTRANS);
        $pembayaran->update(['gateway_order_id' => $pembayaran->kode.'-AB12']);

        $payload = $this->payloadMidtrans($pembayaran, 'settlement', '200');

        $this->postJson(route('webhook.midtrans'), ['signature_key' => 'palsu'] + $payload)->assertForbidden();
        $this->assertSame(Pembayaran::MENUNGGU_PEMBAYARAN, $pembayaran->fresh()->status);

        $this->postJson(route('webhook.midtrans'), $payload)->assertOk();
        $this->postJson(route('webhook.midtrans'), $payload)->assertOk();   // dikirim ulang oleh Midtrans

        $pembayaran->refresh();
        $this->assertSame(Pembayaran::LUNAS, $pembayaran->status);
        $this->assertSame('MT-TRX-001', $pembayaran->gateway_transaction_id);
        $this->assertSame(Transaksi::DIPROSES, $pembayaran->transaksis()->first()->status_transaksi);
    }

    public function test_webhook_midtrans_expire_membatalkan_dan_mengembalikan_stok(): void
    {
        config(['services.midtrans.server_key' => self::SERVER_KEY]);
        [, $pembayaran, $produk] = $this->buatPesanan(metode: Pembayaran::METODE_MIDTRANS);
        $pembayaran->update(['gateway_order_id' => $pembayaran->kode.'-CD34']);

        $this->postJson(route('webhook.midtrans'), $this->payloadMidtrans($pembayaran, 'expire', '407'))->assertOk();

        $this->assertSame(Pembayaran::KEDALUWARSA, $pembayaran->fresh()->status);
        $this->assertSame(10, $produk->fresh()->stok);
    }

    public function test_mode_simulasi_midtrans_menjalankan_alur_yang_sama(): void
    {
        config(['services.midtrans.simulasi' => true]);
        $pembeli = $this->buatUser('user');
        $this->isiKeranjang($pembeli, [[$this->buatProduk(stok: 5), 2]]);

        $this->actingAs($pembeli)->post(route('user.checkout.store'), $this->dataCheckout(Pembayaran::METODE_MIDTRANS));
        $pembayaran = Pembayaran::sole();

        $this->actingAs($pembeli)->get(route('user.pembayaran.show', $pembayaran))->assertOk()->assertSee('Mode simulasi Midtrans');
        $this->actingAs($pembeli)->post(route('user.pembayaran.simulasi', $pembayaran), ['hasil' => 'berhasil'])->assertSessionHas('success');

        $this->assertSame(Pembayaran::LUNAS, $pembayaran->fresh()->status);
    }

    public function test_halaman_marketplace_bisa_dibuka(): void
    {
        [$pembeli, $pembayaran, $produk] = $this->buatPesanan(denganBukti: true);
        KeranjangItem::create(['user_id' => $pembeli->id, 'produk_id' => $produk->id, 'jumlah' => 1]);

        $this->actingAs($pembeli)->get(route('user.marketplace.show', $produk))->assertOk()->assertSee('Tambah ke keranjang');
        $this->actingAs($pembeli)->get(route('user.keranjang.index'))->assertOk()->assertSee($produk->nama_produk);
        $this->actingAs($pembeli)->get(route('user.checkout.create'))->assertOk()->assertSee('Transfer bank');
        $this->actingAs($pembeli)->get(route('user.pesanan'))->assertOk()->assertSee($pembayaran->kode);
        $this->actingAs($pembeli)->get(route('user.pembayaran.show', $pembayaran))->assertOk()->assertSee('Bukti transfer sedang diperiksa');
        $this->actingAs($produk->petani)->get(route('petani.pesanan.index'))->assertOk()->assertSee($produk->nama_produk);
        $this->actingAs($this->buatUser('admin'))->get(route('admin.pembayaran.index'))->assertOk()->assertSee($pembayaran->kode);
    }

    // ------------------------------------------------------------------

    private function buatUser(string $role): User
    {
        return User::create([
            'username' => $role.'_'.Str::lower(Str::random(6)),
            'email' => Str::lower(Str::random(10)).'@contoh.test',
            'password' => 'rahasia123',
            'role' => $role,
        ]);
    }

    private function buatProduk(int $stok, int $harga = 42000): ProdukPanen
    {
        return ProdukPanen::create([
            'petani_id' => $this->buatUser('petani')->id,
            'nama_produk' => 'Produk '.Str::random(5),
            'kategori' => 'Sayuran',
            'deskripsi' => 'Panen pagi',
            'harga' => $harga,
            'satuan' => 'kg',
            'stok' => $stok,
            'status' => 'tersedia',
        ]);
    }

    private function isiKeranjang(User $pembeli, array $daftar): void
    {
        foreach ($daftar as [$produk, $jumlah]) {
            KeranjangItem::create(['user_id' => $pembeli->id, 'produk_id' => $produk->id, 'jumlah' => $jumlah]);
        }
    }

    private function dataCheckout(string $metode = Pembayaran::METODE_TRANSFER): array
    {
        return ['alamat_pengiriman' => 'Jl. Veteran No. 12, Bendosari, Sukoharjo', 'catatan' => 'Kirim pagi', 'metode' => $metode];
    }

    /**
     * Pesanan 3 kg dari produk berstok 10 (sisa 7).
     *
     * @return array{0: User, 1: Pembayaran, 2: ProdukPanen}
     */
    private function buatPesanan(bool $denganBukti = false, string $metode = Pembayaran::METODE_TRANSFER): array
    {
        $pembeli = $this->buatUser('user');
        $produk = $this->buatProduk(stok: 10);
        $this->isiKeranjang($pembeli, [[$produk, 3]]);

        if ($metode === Pembayaran::METODE_MIDTRANS) {
            config(['services.midtrans.simulasi' => true]);
        }

        $pembayaran = app(CheckoutService::class)->checkout($pembeli, $this->dataCheckout($metode));

        if ($denganBukti) {
            $pembayaran->update(['status' => Pembayaran::MENUNGGU_VERIFIKASI, 'bukti_transfer' => 'bukti-transfer/contoh.jpg', 'bukti_diunggah_at' => now()]);
            Storage::disk('local')->put('bukti-transfer/contoh.jpg', 'isi');
        }

        return [$pembeli, $pembayaran->fresh('transaksis'), $produk];
    }

    private function bayarLunas(Pembayaran $pembayaran): void
    {
        $pembayaran->update(['status' => Pembayaran::MENUNGGU_VERIFIKASI]);
        $this->actingAs($this->buatUser('admin'))->post(route('admin.pembayaran.verifikasi', $pembayaran));
    }

    private function payloadMidtrans(Pembayaran $pembayaran, string $status, string $kode): array
    {
        $gross = number_format((float) $pembayaran->jumlah, 2, '.', '');

        return [
            'order_id' => $pembayaran->gateway_order_id,
            'transaction_id' => 'MT-TRX-001',
            'transaction_status' => $status,
            'status_code' => $kode,
            'gross_amount' => $gross,
            'payment_type' => 'bank_transfer',
            'signature_key' => hash('sha512', $pembayaran->gateway_order_id.$kode.$gross.self::SERVER_KEY),
        ];
    }
}
