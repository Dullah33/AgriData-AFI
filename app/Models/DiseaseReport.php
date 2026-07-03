<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiseaseReport extends Model
{
    protected $table = 'disease_reports';

    protected $fillable = [
        'user_id', 'lahan_id', 'plant_id', 'wilayah_id',
        'nama_penyakit', 'confidence_score', 'foto_tanaman',
        'gejala', 'penyebab', 'penanganan', 'tingkat_risiko',
        'latitude', 'longitude', 'status',
        'rekomendasi_tindak_lanjut', 'ditinjau_oleh', 'ditinjau_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence_score' => 'decimal:2',
            'latitude'         => 'decimal:7',
            'longitude'        => 'decimal:7',
            'ditinjau_at'      => 'datetime',
        ];
    }

    // Pelapor (Petani atau User)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lahan()
    {
        return $this->belongsTo(Lahan::class, 'lahan_id');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    // Penyuluh yang meninjau/memberi rekomendasi tindak lanjut
    public function peninjau()
    {
        return $this->belongsTo(User::class, 'ditinjau_oleh');
    }

    public function scopeBaru($query)
    {
        return $query->where('status', 'baru');
    }

    public function scopeDitindaklanjuti($query)
    {
        return $query->where('status', 'ditindaklanjuti');
    }

    // Laporan di wilayah binaan Penyuluh, dicocokkan lewat nama wilayah
    // (konsisten dengan ExtensionOfficer::petaniBinaan()).
    public function scopeDiWilayah($query, string $namaWilayah)
    {
        return $query->whereHas('wilayah', function ($q) use ($namaWilayah) {
            $q->where('nama_wilayah', $namaWilayah);
        });
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function badgeRisiko(): string
    {
        return match ($this->tingkat_risiko) {
            'tinggi' => 'bg-red-100 text-red-700',
            'sedang' => 'bg-amber-100 text-amber-700',
            default  => 'bg-green-100 text-green-700',
        };
    }

    public function badgeStatus(): string
    {
        return match ($this->status) {
            'ditindaklanjuti' => 'bg-green-100 text-green-700',
            'ditinjau'        => 'bg-blue-100 text-blue-700',
            default           => 'bg-gray-100 text-gray-600',
        };
    }
}
