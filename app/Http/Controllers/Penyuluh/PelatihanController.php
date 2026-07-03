<?php

namespace App\Http\Controllers\Penyuluh;

use App\Http\Controllers\Controller;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PelatihanController extends Controller
{
    // GET /penyuluh/pelatihan
    // "Jadwal Pelatihan Kelompok Tani" (BAB 2A.6): daftar & kalender
    // kegiatan pelatihan/penyuluhan kelompok tani.
    public function index()
    {
        $officer = Auth::user()->extensionOfficer;

        if (! $officer) {
            return view('penyuluh.belum-terdaftar');
        }

        $pelatihans = $officer->trainingPrograms()->latest('tanggal_pelaksanaan')->paginate(10);

        return view('penyuluh.pelatihan.index', [
            'pelatihans' => $pelatihans,
        ]);
    }

    // GET /penyuluh/pelatihan/create
    public function create()
    {
        abort_if(! Auth::user()->extensionOfficer, 403, 'Profil Penyuluh belum terdaftar. Hubungi Admin.');

        return view('penyuluh.pelatihan.create');
    }

    // POST /penyuluh/pelatihan
    public function store(Request $request)
    {
        $officer = Auth::user()->extensionOfficer;
        abort_if(! $officer, 403, 'Profil Penyuluh belum terdaftar. Hubungi Admin.');

        $data = $request->validate([
            'judul'                => 'required|string|max:150',
            'deskripsi'            => 'nullable|string|max:2000',
            'lokasi'               => 'required|string|max:150',
            'tanggal_pelaksanaan'  => 'required|date',
            'jumlah_peserta'       => 'nullable|integer|min:0',
        ], [
            'judul.required'               => 'Judul kegiatan harus diisi.',
            'lokasi.required'              => 'Lokasi kegiatan harus diisi.',
            'tanggal_pelaksanaan.required' => 'Tanggal pelaksanaan harus diisi.',
        ]);

        $data['extension_officer_id'] = $officer->id;
        $data['status'] = 'terjadwal';

        TrainingProgram::create($data);

        return redirect()->route('penyuluh.pelatihan.index')
            ->with('success', 'Kegiatan pelatihan berhasil dijadwalkan.');
    }

    // GET /penyuluh/pelatihan/{pelatihan}/edit
    public function edit(TrainingProgram $pelatihan)
    {
        $this->pastikanPemilik($pelatihan);

        return view('penyuluh.pelatihan.edit', [
            'pelatihan' => $pelatihan,
        ]);
    }

    // PUT /penyuluh/pelatihan/{pelatihan}
    public function update(Request $request, TrainingProgram $pelatihan)
    {
        $this->pastikanPemilik($pelatihan);

        $data = $request->validate([
            'judul'                => 'required|string|max:150',
            'deskripsi'            => 'nullable|string|max:2000',
            'lokasi'               => 'required|string|max:150',
            'tanggal_pelaksanaan'  => 'required|date',
            'jumlah_peserta'       => 'nullable|integer|min:0',
            'status'               => 'required|in:terjadwal,selesai,batal',
        ]);

        $pelatihan->update($data);

        return redirect()->route('penyuluh.pelatihan.index')
            ->with('success', 'Data pelatihan berhasil diperbarui.');
    }

    // DELETE /penyuluh/pelatihan/{pelatihan}
    public function destroy(TrainingProgram $pelatihan)
    {
        $this->pastikanPemilik($pelatihan);

        $pelatihan->delete();

        return back()->with('success', 'Kegiatan pelatihan berhasil dihapus.');
    }

    private function pastikanPemilik(TrainingProgram $pelatihan): void
    {
        $officer = Auth::user()->extensionOfficer;
        abort_if(! $officer || $pelatihan->extension_officer_id !== $officer->id, 403);
    }
}
