<?php

namespace App\Services\Marketplace;

use App\Exceptions\KeranjangKosongException;
use App\Exceptions\StokTidakCukupException;
use App\Models\KeranjangItem;
use App\Models\Pembayaran;
use App\Models\ProdukPanen;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Mengubah isi keranjang menjadi pesanan + tagihan pembayaran.
 *
 * Semua langkah berjalan di dalam satu DB::transaction():
 *   1. Kunci baris keranjang dan baris produk (SELECT ... FOR UPDATE),
 *      selalu dalam urutan id yang sama supaya dua checkout bersamaan
 *      tidak saling mengunci (deadlock).
 *   2. Cek ulang stok tiap produk, lalu kurangi stok secara kondisional
 *      (UPDATE ... WHERE stok >= jumlah).
 *   3. Catat tagihan (pembayaran) dan satu baris transaksi per produk,
 *      lengkap dengan harga saat dibeli.
 *   4. Kosongkan keranjang.
 *
 * Kalau satu produk saja gagal (stok kurang, produk ditarik, error
 * database), exception dilempar dan semua perubahan di atas di-rollback:
 * stok produk lain kembali seperti semula dan tidak ada transaksi yatim.
 */
class CheckoutService
{
    /**
     * @param  array{alamat_pengiriman: string, catatan?: ?string, metode: string}  $data
     *
     * @throws KeranjangKosongException
     * @throws StokTidakCukupException
     */
    public function checkout(User $pembeli, array $data): Pembayaran
    {
        return DB::transaction(function () use ($pembeli, $data) {
            $items = KeranjangItem::where('user_id', $pembeli->id)
                ->orderBy('produk_id')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw new KeranjangKosongException();
            }

            $produks = ProdukPanen::whereIn('id', $items->pluck('produk_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $pembayaran = Pembayaran::create([
                'kode' => Pembayaran::buatKode(),
                'user_id' => $pembeli->id,
                'metode' => $data['metode'],
                'jumlah' => 0,
                'status' => Pembayaran::MENUNGGU_PEMBAYARAN,
                'batas_bayar' => now()->addHours(config('marketplace.batas_bayar_jam', 24)),
            ]);

            $total = 0.0;

            foreach ($items as $item) {
                $produk = $produks->get($item->produk_id);

                if (! $produk || ! $produk->bisaDibeli() || $produk->stok < $item->jumlah) {
                    throw new StokTidakCukupException($produk->nama_produk ?? 'Produk', (int) ($produk->stok ?? 0), $item->produk_id);
                }

                // Lapisan pengaman kedua: UPDATE hanya mengubah baris kalau stok
                // masih cukup, jadi stok tidak mungkin minus walau lock tidak
                // didukung driver database (mis. SQLite).
                $berubah = ProdukPanen::whereKey($produk->id)
                    ->where('stok', '>=', $item->jumlah)
                    ->decrement('stok', $item->jumlah);

                if ($berubah === 0) {
                    throw new StokTidakCukupException($produk->nama_produk, (int) $produk->fresh()->stok, $produk->id);
                }

                $subtotal = round((float) $produk->harga * $item->jumlah, 2);

                Transaksi::create([
                    'user_id' => $pembeli->id,
                    'produk_id' => $produk->id,
                    'pembayaran_id' => $pembayaran->id,
                    'jumlah' => $item->jumlah,
                    'harga_satuan' => $produk->harga,
                    'total_harga' => $subtotal,
                    'status_transaksi' => Transaksi::MENUNGGU,
                    'alamat_pengiriman' => $data['alamat_pengiriman'],
                    'catatan' => $data['catatan'] ?? null,
                ]);

                $total += $subtotal;
            }

            $pembayaran->update(['jumlah' => $total]);

            KeranjangItem::where('user_id', $pembeli->id)->delete();

            return $pembayaran;
        });
    }
}
