<?php

namespace App\Http\Controllers\Penyuluh;

use App\Http\Controllers\Controller;
use App\Models\FieldVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KunjunganController extends Controller
{
    // GET /penyuluh/kunjungan
    // Menggabungkan "Jadwal Kunjungan Lapangan" dan "Laporan Hasil
    // Kunjungan" (BAB 2A.6) dalam satu halaman list, dibedakan lewat
    // kolom status — sesuai desain tabel field_visits (satu baris bisa
    // berupa jadwal yang belum dilaksanakan maupun laporan yang sudah selesai).
    public function index(Request $request)
    {
        $officer = Auth::user()->extensionOfficer;

        if (! $officer) {
            return view('penyuluh.belum-terdaftar');
        }

        $query = $officer->fieldVisits()->with('petaniProfile.user')->latest('tanggal_kunjungan');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $kunjungans = $query->paginate(10)->withQueryString();

        return view('penyuluh.kunjungan.index', [
            'kunjungans' => $kunjungans,
        ]);
    }

    // GET /penyuluh/kunjungan/create
    // Form penjadwalan kunjungan baru: pilih petani binaan, tanggal, catatan persiapan.
    public function create()
    {
        $officer = Auth::user()->extensionOfficer;
        abort_if(! $officer, 403, 'Profil Penyuluh belum terdaftar. Hubungi Admin.');

        return view('penyuluh.kunjungan.create', [
            'petaniBinaan' => $officer->petaniBinaan(),
        ]);
    }

    // POST /penyuluh/kunjungan
    public function store(Request $request)
    {
        $officer = Auth::user()->extensionOfficer;
        abort_if(! $officer, 403, 'Profil Penyuluh belum terdaftar. Hubungi Admin.');

        $data = $request->validate([
            'petani_profile_id' => 'required|exists:petani_profile,id',
            'tanggal_kunjungan' => 'required|date',
            'catatan_persiapan' => 'nullable|string|max:1000',
        ], [
            'petani_profile_id.required' => 'Pilih petani yang akan dikunjungi.',
            'tanggal_kunjungan.required' => 'Tanggal kunjungan harus diisi.',
        ]);

        $data['extension_officer_id'] = $officer->id;
        $data['status'] = 'terjadwal';

        FieldVisit::create($data);

        return redirect()->route('penyuluh.kunjungan.index')
            ->with('success', 'Jadwal kunjungan berhasil dibuat.');
    }

    // GET /penyuluh/kunjungan/{kunjungan}/laporkan
    // Form Laporan Kunjungan (BAB 2A.6): diisi setelah kunjungan
    // dilaksanakan (kondisi lahan, kendala, rekomendasi, foto).
    public function laporkan(FieldVisit $kunjungan)
    {
        $this->pastikanPemilik($kunjungan);

        return view('penyuluh.kunjungan.laporkan', [
            'kunjungan' => $kunjungan->load('petaniProfile.user'),
        ]);
    }

    // POST /penyuluh/kunjungan/{kunjungan}/laporkan
    public function simpanLaporan(Request $request, FieldVisit $kunjungan)
    {
        $this->pastikanPemilik($kunjungan);

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
            $data['foto_dokumentasi'] = $request->file('foto_dokumentasi')
                ->store('kunjungan-penyuluh', 'public');
        }

        $data['status'] = 'selesai';

        $kunjungan->update($data);

        return redirect()->route('penyuluh.kunjungan.index')
            ->with('success', 'Laporan kunjungan berhasil disimpan.');
    }

    // POST /penyuluh/kunjungan/{kunjungan}/batalkan
    public function batalkan(FieldVisit $kunjungan)
    {
        $this->pastikanPemilik($kunjungan);

        $kunjungan->update(['status' => 'batal']);

        return back()->with('success', 'Jadwal kunjungan dibatalkan.');
    }

    private function pastikanPemilik(FieldVisit $kunjungan): void
    {
        $officer = Auth::user()->extensionOfficer;
        abort_if(! $officer || $kunjungan->extension_officer_id !== $officer->id, 403);
    }
}
