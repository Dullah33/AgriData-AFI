<?php

namespace App\Http\Controllers\Penyuluh;

use App\Http\Controllers\Concerns\MembutuhkanPenyuluh;
use App\Http\Controllers\Controller;
use App\Models\MonthlyReport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LaporanBulananController extends Controller
{
    use MembutuhkanPenyuluh;

    // GET /penyuluh/laporan-bulanan
    // "Laporan Kinerja Bulanan" (BAB 2A.6 & BAB 2.7): riwayat laporan
    // bulanan yang disusun & dikirim ke Admin.
    public function index()
    {
        if (! $officer = $this->penyuluhAtauNull()) {
            return view('penyuluh.belum-terdaftar');
        }

        $laporans = $officer->monthlyReports()->latest('tahun')->latest('bulan')->paginate(10);

        return view('penyuluh.laporan-bulanan.index', [
            'laporans' => $laporans,
        ]);
    }

    // GET /penyuluh/laporan-bulanan/create
    // Ringkasan otomatis (jumlah kunjungan & pelatihan bulan berjalan)
    // ditampilkan sebagai bantuan pengisian, bukan disimpan manual.
    public function create()
    {
        $officer = $this->penyuluh();

        return view('penyuluh.laporan-bulanan.create', [
            'jumlahKunjunganBulanIni' => $officer->fieldVisits()->bulanIni()->count(),
            'jumlahPelatihanBulanIni' => $officer->trainingPrograms()->bulanIni()->count(),
        ]);
    }

    // POST /penyuluh/laporan-bulanan
    public function store(Request $request)
    {
        $officer = $this->penyuluh();

        $data = $request->validate([
            // Satu laporan per bulan (ada unique index di database). Tanpa aturan
            // ini, laporan kedua untuk bulan yang sama berakhir error 500.
            'bulan'                  => [
                'required', 'integer', 'between:1,12',
                Rule::unique('monthly_reports')->where(fn ($q) => $q
                    ->where('extension_officer_id', $officer->id)
                    ->where('tahun', $request->integer('tahun'))),
            ],
            'tahun'                  => 'required|integer|min:2020|max:2100',
            'ringkasan_kegiatan'     => 'required|string|max:3000',
            'kendala'                => 'nullable|string|max:2000',
            'rencana_tindak_lanjut'  => 'nullable|string|max:2000',
        ], [
            'ringkasan_kegiatan.required' => 'Ringkasan kegiatan harus diisi.',
            'bulan.unique'                => 'Laporan untuk bulan dan tahun ini sudah ada. Ubah draft yang sudah ada.',
        ]);

        $officer->monthlyReports()->create($data + ['status' => 'draft']);

        return redirect()->route('penyuluh.laporan-bulanan.index')
            ->with('success', 'Laporan bulanan disimpan sebagai draft.');
    }

    // GET /penyuluh/laporan-bulanan/{laporanBulanan}/edit
    public function edit(MonthlyReport $laporanBulanan)
    {
        $this->pastikanMilikPenyuluh($laporanBulanan);
        abort_if($laporanBulanan->status === 'terkirim', 403, 'Laporan yang sudah terkirim tidak bisa diedit.');

        return view('penyuluh.laporan-bulanan.edit', [
            'laporan' => $laporanBulanan,
        ]);
    }

    // PUT /penyuluh/laporan-bulanan/{laporanBulanan}
    public function update(Request $request, MonthlyReport $laporanBulanan)
    {
        $this->pastikanMilikPenyuluh($laporanBulanan);
        abort_if($laporanBulanan->status === 'terkirim', 403, 'Laporan yang sudah terkirim tidak bisa diedit.');

        $data = $request->validate([
            'ringkasan_kegiatan'     => 'required|string|max:3000',
            'kendala'                => 'nullable|string|max:2000',
            'rencana_tindak_lanjut'  => 'nullable|string|max:2000',
        ]);

        $laporanBulanan->update($data);

        return redirect()->route('penyuluh.laporan-bulanan.index')
            ->with('success', 'Draft laporan bulanan berhasil diperbarui.');
    }

    // POST /penyuluh/laporan-bulanan/{laporanBulanan}/kirim
    // Kirim laporan final ke Admin — setelah ini tidak bisa diedit lagi.
    public function kirim(MonthlyReport $laporanBulanan)
    {
        $this->pastikanMilikPenyuluh($laporanBulanan);

        $laporanBulanan->update([
            'status'       => 'terkirim',
            'submitted_at' => now(),
        ]);

        return redirect()->route('penyuluh.laporan-bulanan.index')
            ->with('success', 'Laporan bulanan berhasil dikirim ke Admin.');
    }
}
