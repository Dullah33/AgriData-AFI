<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingProgram extends Model
{
    protected $table = 'training_programs';

    protected $fillable = [
        'extension_officer_id', 'judul', 'deskripsi', 'lokasi',
        'tanggal_pelaksanaan', 'jumlah_peserta', 'status', 'foto_dokumentasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pelaksanaan' => 'date',
        ];
    }

    public function extensionOfficer()
    {
        return $this->belongsTo(ExtensionOfficer::class, 'extension_officer_id');
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal_pelaksanaan', now()->month)
            ->whereYear('tanggal_pelaksanaan', now()->year);
    }
}
