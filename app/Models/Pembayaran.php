<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Tagihan dari satu kali checkout. Menaungi beberapa baris Transaksi.
 *
 * Alur status:
 *   menunggu_pembayaran --(unggah bukti)--> menunggu_verifikasi --(admin setuju)--> lunas
 *            ^                                      |
 *            +-----------(admin tolak bukti)--------+
 *
 *   menunggu_pembayaran --(Midtrans settlement)--> lunas
 *   menunggu_pembayaran --> dibatalkan | kedaluwarsa | gagal  (stok dikembalikan)
 *
 * Perubahan status dilakukan lewat App\Services\Marketplace\PembayaranService.
 */
class Pembayaran extends Model
{
    public const MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const LUNAS = 'lunas';
    public const DIBATALKAN = 'dibatalkan';
    public const KEDALUWARSA = 'kedaluwarsa';
    public const GAGAL = 'gagal';

    public const METODE_TRANSFER = 'transfer_manual';
    public const METODE_MIDTRANS = 'midtrans';

    public const LABEL_STATUS = [
        self::MENUNGGU_PEMBAYARAN => 'Menunggu pembayaran',
        self::MENUNGGU_VERIFIKASI => 'Menunggu verifikasi',
        self::LUNAS => 'Lunas',
        self::DIBATALKAN => 'Dibatalkan',
        self::KEDALUWARSA => 'Kedaluwarsa',
        self::GAGAL => 'Gagal',
    ];

    public const LABEL_METODE = [
        self::METODE_TRANSFER => 'Transfer bank',
        self::METODE_MIDTRANS => 'Midtrans (VA, e-wallet, kartu)',
    ];

    // Status akhir: tidak bisa berubah lagi
    public const STATUS_FINAL = [self::LUNAS, self::DIBATALKAN, self::KEDALUWARSA, self::GAGAL];

    protected $table = 'pembayaran';

    protected $fillable = [
        'kode', 'user_id', 'metode', 'jumlah', 'status', 'batas_bayar',
        'bukti_transfer', 'bukti_diunggah_at', 'diverifikasi_oleh', 'diverifikasi_at', 'catatan_verifikasi',
        'gateway_order_id', 'snap_token', 'gateway_transaction_id', 'gateway_payload',
        'dibayar_at',
    ];

    protected $attributes = [
        'status' => self::MENUNGGU_PEMBAYARAN,
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'batas_bayar' => 'datetime',
            'bukti_diunggah_at' => 'datetime',
            'diverifikasi_at' => 'datetime',
            'dibayar_at' => 'datetime',
            'gateway_payload' => 'array',
        ];
    }

    // URL memakai kode invoice, bukan id berurutan yang mudah ditebak
    public function getRouteKeyName(): string
    {
        return 'kode';
    }

    public static function buatKode(): string
    {
        do {
            $kode = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (static::where('kode', $kode)->exists());

        return $kode;
    }

    public function pembeli()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'pembayaran_id');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // Belum dibayar dan sudah lewat batas waktu
    public function scopeJatuhTempo(Builder $query): Builder
    {
        return $query->where('status', self::MENUNGGU_PEMBAYARAN)
            ->whereNotNull('batas_bayar')
            ->where('batas_bayar', '<', now());
    }

    public function labelStatus(): string
    {
        return self::LABEL_STATUS[$this->status] ?? Str::headline((string) $this->status);
    }

    public function labelMetode(): string
    {
        return self::LABEL_METODE[$this->metode] ?? Str::headline((string) $this->metode);
    }

    public function sudahFinal(): bool
    {
        return in_array($this->status, self::STATUS_FINAL, true);
    }

    public function lewatBatasBayar(): bool
    {
        return $this->batas_bayar !== null && $this->batas_bayar->isPast();
    }

    public function bisaDibayar(): bool
    {
        return $this->status === self::MENUNGGU_PEMBAYARAN && ! $this->lewatBatasBayar();
    }

    public function bisaUnggahBukti(): bool
    {
        return $this->metode === self::METODE_TRANSFER && $this->bisaDibayar();
    }

    public function bisaDibatalkanPembeli(): bool
    {
        return $this->status === self::MENUNGGU_PEMBAYARAN;
    }
}
