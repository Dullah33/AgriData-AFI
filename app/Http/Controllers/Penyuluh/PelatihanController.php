<?php

namespace App\Http\Controllers\Penyuluh;

use App\Http\Controllers\Concerns\MembutuhkanPenyuluh;
use App\Http\Controllers\Controller;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;

class PelatihanController extends Controller
{
    use MembutuhkanPenyuluh;

    // GET /penyuluh/pelatihan
    // "Jadwal Pelatihan Kelompok Tani" (BAB 2A.6): daftar & kalender
    // kegiatan pelatihan/penyuluhan kelompok tani.
    public function index()
    {
        if (! $officer = $this->penyuluhAtauNull()) {
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
        $this->penyuluh();

        return view('penyuluh.pelatihan.create');
    }

    // POST /penyuluh/pelatihan
    public function store(Request $request)
    {
        $officer = $this->penyuluh();

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

        $officer->trainingPrograms()->create($data + ['status' => 'terjadwal']);

        return redirect()->route('penyuluh.pelatihan.index')
            ->with('success', 'Kegiatan pelatihan berhasil dijadwalkan.');
    }

    // GET /penyuluh/pelatihan/{pelatihan}/edit
    public function edit(TrainingProgram $pelatihan)
    {
        $this->pastikanMilikPenyuluh($pelatihan);

        return view('penyuluh.pelatihan.edit', [
            'pelatihan' => $pelatihan,
        ]);
    }

    // PUT /penyuluh/pelatihan/{pelatihan}
    public function update(Request $request, TrainingProgram $pelatihan)
    {
        $this->pastikanMilikPenyuluh($pelatihan);

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
        $this->pastikanMilikPenyuluh($pelatihan);

        $pelatihan->delete();

        return back()->with('success', 'Kegiatan pelatihan berhasil dihapus.');
    }
}
