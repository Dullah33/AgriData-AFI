<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Http\Requests\Petani\LahanRequest;
use App\Models\Lahan;
use App\Models\PetaniProfile;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LahanController extends Controller
{
    // GET /petani/lahan
    // Halaman "Profil & Lahan Saya" (BAB 2A.3): daftar seluruh lahan
    // milik petani yang sedang login.
    public function index(WeatherService $weather)
    {
        $petaniProfile = Auth::user()->petaniProfile;

        // Jika akun petani belum punya profil (belum didaftarkan Admin
        // via Manajemen Data Petani), tampilkan pesan alih-alih error.
        if (! $petaniProfile) {
            return view('petani.lahan.belum-profil');
        }

        $petaniProfile->loadMissing('wilayah');
        $lahans = $petaniProfile->lahans()->with('weatherData')->latest()->get()
            ->each->setRelation('petaniProfile', $petaniProfile);

        // Cuaca tiap lahan diambil dari cache (3 jam). Lahan yang berdekatan
        // berbagi satu entri cache, jadi API paling banyak dipanggil sekali
        // per titik lokasi meski petani punya banyak lahan.
        $cuaca = $lahans->mapWithKeys(fn (Lahan $lahan) => [$lahan->id => $weather->untukLahan($lahan)]);

        return view('petani.lahan.index', [
            'petaniProfile' => $petaniProfile,
            'lahans'        => $lahans,
            'cuaca'         => $cuaca,
        ]);
    }

    // GET /petani/lahan/create
    public function create()
    {
        $this->profilPetani();

        return view('petani.lahan.create', ['jenisTanahList' => Lahan::JENIS_TANAH]);
    }

    // POST /petani/lahan
    public function store(LahanRequest $request)
    {
        $data = $request->dataLahan();

        $this->profilPetani()->lahans()->create($data + [
            'status' => filled($data['tanaman_aktif'] ?? null) ? 'aktif' : 'bera',
        ]);

        return redirect()->route('petani.lahan.index')
            ->with('success', 'Lahan berhasil ditambahkan.');
    }

    // GET /petani/lahan/{lahan}/edit
    public function edit(Lahan $lahan)
    {
        Gate::authorize('update', $lahan);

        return view('petani.lahan.edit', [
            'lahan'          => $lahan,
            'jenisTanahList' => Lahan::JENIS_TANAH,
        ]);
    }

    // PUT /petani/lahan/{lahan}
    public function update(LahanRequest $request, Lahan $lahan)
    {
        Gate::authorize('update', $lahan);

        $lahan->update($request->dataLahan());

        return redirect()->route('petani.lahan.index')
            ->with('success', 'Data lahan berhasil diperbarui.');
    }

    // DELETE /petani/lahan/{lahan}
    public function destroy(Lahan $lahan)
    {
        Gate::authorize('delete', $lahan);

        $lahan->delete();

        return back()->with('success', 'Lahan berhasil dihapus.');
    }

    // Akun petani yang belum didaftarkan profilnya oleh admin belum bisa menambah lahan
    private function profilPetani(): PetaniProfile
    {
        $profil = Auth::user()->petaniProfile;
        abort_if(! $profil, 403, 'Profil petani belum terdaftar. Hubungi Admin.');

        return $profil;
    }
}
