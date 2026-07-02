<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\Lahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LahanController extends Controller
{
    private array $jenisTanahList = [
        'Aluvial', 'Andosol', 'Latosol', 'Regosol', 'Grumusol', 'Podsolik', 'Lainnya',
    ];

    // GET /petani/lahan
    // Halaman "Profil & Lahan Saya" (BAB 2A.3): daftar seluruh lahan
    // milik petani yang sedang login.
    public function index()
    {
        $petaniProfile = Auth::user()->petaniProfile;

        // Jika akun petani belum punya profil (belum didaftarkan Admin
        // via Manajemen Data Petani), tampilkan pesan alih-alih error.
        if (! $petaniProfile) {
            return view('petani.lahan.belum-profil');
        }

        $lahans = $petaniProfile->lahans()->latest()->get();

        return view('petani.lahan.index', [
            'petaniProfile' => $petaniProfile,
            'lahans'        => $lahans,
        ]);
    }

    // GET /petani/lahan/create
    public function create()
    {
        abort_if(! Auth::user()->petaniProfile, 403, 'Profil petani belum terdaftar. Hubungi Admin.');

        return view('petani.lahan.create', [
            'jenisTanahList' => $this->jenisTanahList,
        ]);
    }

    // POST /petani/lahan
    public function store(Request $request)
    {
        $petaniProfile = Auth::user()->petaniProfile;
        abort_if(! $petaniProfile, 403, 'Profil petani belum terdaftar. Hubungi Admin.');

        $data = $request->validate([
            'nama_lahan'          => 'required|string|max:100',
            'luas_ha'             => 'required|numeric|min:0.01',
            'jenis_tanah'         => 'nullable|string|max:50',
            'tanaman_aktif'       => 'nullable|string|max:100',
            'tanggal_tanam'       => 'nullable|date',
            'perkiraan_panen'     => 'nullable|date|after_or_equal:tanggal_tanam',
            'koordinat_poligon'   => 'nullable|string',
        ], [
            'nama_lahan.required' => 'Nama/kode lahan harus diisi.',
            'luas_ha.required'    => 'Luas lahan harus diisi.',
            'luas_ha.min'         => 'Luas lahan minimal 0.01 hektar.',
        ]);

        $data['petani_profile_id'] = $petaniProfile->id;
        $data['status'] = $data['tanaman_aktif'] ?? null ? 'aktif' : 'bera';
        $data['koordinat_poligon'] = $this->decodePoligon($request->input('koordinat_poligon'));

        Lahan::create($data);

        return redirect()->route('petani.lahan.index')
            ->with('success', 'Lahan berhasil ditambahkan.');
    }

    // GET /petani/lahan/{lahan}/edit
    public function edit(Lahan $lahan)
    {
        $this->pastikanPemilik($lahan);

        return view('petani.lahan.edit', [
            'lahan'          => $lahan,
            'jenisTanahList' => $this->jenisTanahList,
        ]);
    }

    // PUT /petani/lahan/{lahan}
    public function update(Request $request, Lahan $lahan)
    {
        $this->pastikanPemilik($lahan);

        $data = $request->validate([
            'nama_lahan'          => 'required|string|max:100',
            'luas_ha'             => 'required|numeric|min:0.01',
            'jenis_tanah'         => 'nullable|string|max:50',
            'tanaman_aktif'       => 'nullable|string|max:100',
            'tanggal_tanam'       => 'nullable|date',
            'perkiraan_panen'     => 'nullable|date|after_or_equal:tanggal_tanam',
            'status'              => 'required|in:aktif,bera,panen',
            'koordinat_poligon'   => 'nullable|string',
        ]);

        // Kalau field poligon tidak dikirim sama sekali (mis. peta belum
        // digambar ulang saat edit), pertahankan poligon lama alih-alih menimpanya jadi null.
        if ($request->filled('koordinat_poligon')) {
            $data['koordinat_poligon'] = $this->decodePoligon($request->input('koordinat_poligon'));
        } else {
            unset($data['koordinat_poligon']);
        }

        $lahan->update($data);

        return redirect()->route('petani.lahan.index')
            ->with('success', 'Data lahan berhasil diperbarui.');
    }

    // DELETE /petani/lahan/{lahan}
    public function destroy(Lahan $lahan)
    {
        $this->pastikanPemilik($lahan);

        $lahan->delete();

        return back()->with('success', 'Lahan berhasil dihapus.');
    }

    // Pastikan lahan yang diakses benar-benar milik petani yang sedang login
    private function pastikanPemilik(Lahan $lahan): void
    {
        $petaniProfile = Auth::user()->petaniProfile;
        abort_if(! $petaniProfile || $lahan->petani_profile_id !== $petaniProfile->id, 403);
    }

    // Widget peta (Leaflet + Leaflet.draw) mengirim koordinat poligon sebagai
    // string JSON lewat hidden input, berbentuk array pasangan [lat, lng].
    // Di-decode di sini (bukan pakai cast otomatis) supaya kita bisa validasi
    // isinya benar-benar array yang valid sebelum disimpan ke kolom JSON.
    private function decodePoligon(?string $json): ?array
    {
        if (! $json) {
            return null;
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded) || count($decoded) < 3) {
            // Poligon butuh minimal 3 titik. Kalau tidak valid, abaikan saja
            // (jangan gagalkan penyimpanan hanya karena peta belum digambar).
            return null;
        }

        return $decoded;
    }
}