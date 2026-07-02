<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyReport extends Model
{
    protected $table = 'monthly_reports';

    protected $fillable = [
        'extension_officer_id', 'bulan', 'tahun', 'ringkasan_kegiatan',
        'kendala', 'rencana_tindak_lanjut', 'status', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function extensionOfficer()
    {
        return $this->belongsTo(ExtensionOfficer::class, 'extension_officer_id');
    }

    // Nama bulan dalam Bahasa Indonesia, misal "Juli 2026"
    public function getPeriodeLabelAttribute(): string
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return ($namaBulan[$this->bulan] ?? $this->bulan) . ' ' . $this->tahun;
    }
}
