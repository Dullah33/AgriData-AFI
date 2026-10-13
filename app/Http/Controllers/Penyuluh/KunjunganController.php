<?php

namespace App\Http\Controllers\Penyuluh;

use App\Http\Controllers\Concerns\MembutuhkanPenyuluh;
use App\Http\Controllers\Controller;
use App\Models\FieldVisit;
use App\Services\PenyimpananFoto;
use Closure;
use Illuminate\Http\Request;

class KunjunganController extends Controller
{
    use MembutuhkanPenyuluh;

    // GET /penyuluh/kunjungan
    // "Jadwal Kunjungan Lapangan" (BAB 2A.6): rencana kunjungan yang BELUM
    // selesai (terjadwal/batal). Yang sudah selesai ada di riwayat().
    public function index(Request $request)
    {
        if (! $officer = $this->penyuluhAtauNull()) {
            return view('penyuluh.belum-terdaftar');
        }

        $kunjungans = $officer->fieldVisits()->with('petaniProfile.user')
            ->whereIn('status', ['terjadwal', 'batal'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('tanggal_kunjungan')
            ->paginate(10)
            ->withQueryString();

        return view('penyuluh.kunjungan.index', ['kunjungans' => $kunjungans]);
    }

    // GET /penyuluh/kunjungan/laporan
    // "Laporan Hasil Kunjungan" (BAB 2A.6): kunjungan yang SUDAH selesai.
    public function riwayat()
    {
        if (! $officer = $this->penyuluhAtauNull()) {
            return view('penyuluh.belum-terdaftar');
        }

        $kunjungans = $officer->fieldVisits()->with('petaniProfile.user')
            ->where('status', 'selesai')
            ->latest('tanggal_kunjungan')
            ->paginate(10);

        return view('penyuluh.kunjungan.riwayat', ['kunjungans' => $kunjungans]);
    }

    // GET /penyuluh/kunjungan/create
    public function create()
    {
        return view('penyuluh.kunjungan.create', [
            'petaniBinaan' => $this->penyuluh()->petaniBinaan(),
        ]);
    }

    // POST /penyuluh/kunjungan
    public function store(Request $request)
    {
        $officer = $this->penyuluh();

        $data = $request->validate([
            'petani_profile_id' => [
                'required', 'integer',
                // Hanya petani di wilayah binaan sendiri. Sebelumnya cukup
                // "exists", jadi penyuluh bisa menjadwalkan petani wilayah lain.
                function (string $atribut, mixed $nilai, Closure $gagal) use ($officer) {
                    if (! $officer->petaniBinaanQuery()->whereKey($nilai)->exists()) {
                        $gagal('Petani ini tidak berada di wilayah binaan Anda.');
                    }
                },
            ],
            'tanggal_kunjungan' => 'required|date',
            'catatan_persiapan' => 'nullable|string|max:1000',
        ], [
            'petani_profile_id.required' => 'Pilih petani yang akan dikunjungi.',
            'tanggal_kunjungan.required' => 'Tanggal kunjungan harus diisi.',
        ]);

        $officer->fieldVisits()->create($data + ['status' => 'terjadwal']);

        return redirect()->route('penyuluh.kunjungan.index')
            ->with('success', 'Jadwal kunjungan berhasil dibuat.');
    }

    // GET /penyuluh/kunjungan/{kunjungan}/laporkan
    // Form Laporan Kunjungan (BAB 2A.6): kondisi lahan, kendala, rekomendasi, foto.
    public function laporkan(FieldVisit $kunjungan)
    {
        $this->pastikanMilikPenyuluh($kunjungan);

        return view('penyuluh.kunjungan.laporkan', [
            'kunjungan' => $kunjungan->load('petaniProfile.user'),
        ]);
    }

    // POST /penyuluh/kunjungan/{kunjungan}/laporkan
    public function simpanLaporan(Request $request, FieldVisit $kunjungan, PenyimpananFoto $foto)
    {
        $this->pastikanMilikPenyuluh($kunjungan);

        $data = $request->validate([
            'kondisi_lahan'     => 'required|string|max:2000',
            'kendala_ditemukan' => 'nullable|string|max:2000',
            'rekomendasi'       => 'required|string|max:2000',
            'foto_dokumentasi'  => 'nullable|image|max:5120',
        ], [
            'kondisi_lahan.required' => 'Kondisi lahan harus diisi.',
            'rekomendasi.required'   => 'Rekomendasi harus diisi.',
        ]);

        if ($request->hasFile('foto_dokumentasi')) {
            $data['foto_dokumentasi'] = $foto->ganti($request->file('foto_dokumentasi'), 'kunjungan-penyuluh', $kunjungan->foto_dokumentasi);
        }

        $kunjungan->update($data + ['status' => 'selesai']);

        return redirect()->route('penyuluh.kunjungan.riwayat')
            ->with('success', 'Laporan kunjungan berhasil disimpan.');
    }

    // POST /penyuluh/kunjungan/{kunjungan}/batalkan
    public function batalkan(FieldVisit $kunjungan)
    {
        $this->pastikanMilikPenyuluh($kunjungan);

        $kunjungan->update(['status' => 'batal']);

        return back()->with('success', 'Jadwal kunjungan dibatalkan.');
    }
}
