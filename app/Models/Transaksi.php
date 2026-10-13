<?php

namespace App\Models;

use App\Exceptions\TransisiStatusTidakValidException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Satu baris transaksi = satu produk dalam satu pesanan.
 *
 * Alur status (nilai di database tetap memakai enum lama):
 *
 *     pending (Menunggu) --> diproses (Diproses) --> selesai (Selesai)
 *            \
 *             +--> batal (Dibatalkan, stok dikembalikan)
 *
 * - Menunggu  : pesanan dibuat, menunggu pembayaran / verifikasi.
 * - Diproses  : pembayaran lunas, petani menyiapkan dan mengirim.
 * - Selesai   : pesanan sudah sampai. Pembeli bisa memberi ulasan.
 * - Dibatalkan: dibatalkan pembeli, ditolak petani, atau lewat batas bayar.
 *
 * Perubahan status selalu lewat method tandaiDiproses(), tandaiSelesai(),
 * dan batalkan() supaya aturan transisi dan pengembalian stok terjamin.
 */
class Transaksi extends Model
{
    public const MENUNGGU = 'pending';
    public const DIPROSES = 'diproses';
    public const SELESAI = 'selesai';
    public const BATAL = 'batal';

    public const LABEL_STATUS = [
        self::MENUNGGU => 'Menunggu',
        self::DIPROSES => 'Diproses',
        self::SELESAI => 'Selesai',
        self::BATAL => 'Dibatalkan',
    ];

    // Status asal => status tujuan yang diizinkan
    private const TRANSISI = [
        self::MENUNGGU => [self::DIPROSES, self::BATAL],
        self::DIPROSES => [self::SELESAI],
        self::SELESAI => [],
        self::BATAL => [],
    ];

    protected $table = 'transaksi';

    protected $fillable = [
        'user_id', 'produk_id', 'pembayaran_id',
        'jumlah', 'harga_satuan', 'total_harga',
        'status_transaksi', 'alamat_pengiriman', 'catatan',
        'alasan_batal', 'diproses_at', 'selesai_at', 'dibatalkan_at',
    ];

    protected $attributes = [
        'status_transaksi' => self::MENUNGGU,
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_satuan' => 'decimal:2',
            'total_harga' => 'decimal:2',
            'diproses_at' => 'datetime',
            'selesai_at' => 'datetime',
            'dibatalkan_at' => 'datetime',
        ];
    }

    public function pembeli()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function produk()
    {
        return $this->belongsTo(ProdukPanen::class, 'produk_id');
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
    }

    public function ulasan()
    {
        return $this->hasOne(UlasanRating::class, 'transaksi_id');
    }

    public function scopeMilikPembeli(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // Transaksi atas produk milik petani tertentu
    public function scopeUntukPetani(Builder $query, int $petaniId): Builder
    {
        return $query->whereHas('produk', fn ($q) => $q->where('petani_id', $petaniId));
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status_transaksi', $status);
    }

    public function labelStatus(): string
    {
        return self::LABEL_STATUS[$this->status_transaksi] ?? ucfirst((string) $this->status_transaksi);
    }

    public function bisaBerubahKe(string $tujuan): bool
    {
        return in_array($tujuan, self::TRANSISI[$this->status_transaksi] ?? [], true);
    }

    public function pastikanBisaBerubahKe(string $tujuan): void
    {
        if (! $this->bisaBerubahKe($tujuan)) {
            throw new TransisiStatusTidakValidException(sprintf(
                'Pesanan berstatus "%s" tidak bisa diubah menjadi "%s".',
                $this->labelStatus(),
                self::LABEL_STATUS[$tujuan] ?? $tujuan,
            ));
        }
    }

    public function tandaiDiproses(): void
    {
        $this->ubahStatus(self::DIPROSES, ['diproses_at' => now()]);
    }

    public function tandaiSelesai(): void
    {
        $this->ubahStatus(self::SELESAI, ['selesai_at' => now()]);
    }

    /**
     * Batalkan transaksi dan kembalikan stok produk. Baris transaksi dikunci
     * dulu supaya dua pembatalan yang terjadi bersamaan tidak mengembalikan
     * stok dua kali. Aman dipanggil di dalam DB::transaction lain.
     */
    public function batalkan(?string $alasan = null): void
    {
        $this->ubahStatus(self::BATAL, [
            'alasan_batal' => $alasan,
            'dibatalkan_at' => now(),
        ], function (self $transaksi) {
            ProdukPanen::whereKey($transaksi->produk_id)->increment('stok', $transaksi->jumlah);
        });
    }

    private function ubahStatus(string $tujuan, array $tambahan = [], ?\Closure $efek = null): void
    {
        DB::transaction(function () use ($tujuan, $tambahan, $efek) {
            $segar = static::whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            $segar->pastikanBisaBerubahKe($tujuan);

            if ($efek) {
                $efek($segar);
            }

            $segar->update(['status_transaksi' => $tujuan] + $tambahan);
            $this->setRawAttributes($segar->getAttributes(), true);
        });
    }
}
