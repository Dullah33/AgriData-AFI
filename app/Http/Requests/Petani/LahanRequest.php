<?php

namespace App\Http\Requests\Petani;

use App\Models\Lahan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Tambah & ubah lahan. Widget peta (Leaflet.draw) mengirim batas lahan
 * sebagai string JSON [[lat, lng], ...] lewat hidden input.
 */
class LahanRequest extends FormRequest
{
    private const MAKS_TITIK = 500;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $aturan = [
            'nama_lahan'        => ['required', 'string', 'max:100'],
            'luas_ha'           => ['required', 'numeric', 'min:0.01'],
            'jenis_tanah'       => ['nullable', Rule::in(Lahan::JENIS_TANAH)],
            'tanaman_aktif'     => ['nullable', 'string', 'max:100'],
            'tanggal_tanam'     => ['nullable', 'date'],
            'perkiraan_panen'   => ['nullable', 'date', 'after_or_equal:tanggal_tanam'],
            'koordinat_poligon' => ['nullable', 'string', 'max:30000'],
        ];

        if ($this->route('lahan')) {
            $aturan['status'] = ['required', 'in:aktif,bera,panen'];
        }

        return $aturan;
    }

    public function messages(): array
    {
        return [
            'nama_lahan.required' => 'Nama/kode lahan harus diisi.',
            'luas_ha.required'    => 'Luas lahan harus diisi.',
            'luas_ha.min'         => 'Luas lahan minimal 0.01 hektar.',
        ];
    }

    /**
     * Data siap simpan. Poligon hanya ikut kalau valid; kalau kosong atau
     * rusak, field ini tidak disertakan sehingga poligon lama tidak tertimpa
     * (dan lahan tetap tersimpan meski peta belum digambar).
     */
    public function dataLahan(): array
    {
        $data = $this->safe()->except('koordinat_poligon');

        if ($poligon = $this->poligon()) {
            $data['koordinat_poligon'] = $poligon;
        }

        return $data;
    }

    /**
     * Minimal 3 titik, tiap titik pasangan angka lat/lng dalam rentang wajar.
     * Sebelumnya isi array tidak dicek, sehingga data asal-asalan ikut
     * tersimpan dan merusak perhitungan titik tengah untuk cuaca.
     */
    public function poligon(): ?array
    {
        $titik = json_decode((string) $this->input('koordinat_poligon'), true);

        if (! is_array($titik) || count($titik) < 3 || count($titik) > self::MAKS_TITIK) {
            return null;
        }

        foreach ($titik as $p) {
            if (! is_array($p) || ! is_numeric($p[0] ?? null) || ! is_numeric($p[1] ?? null)
                || abs($p[0]) > 90 || abs($p[1]) > 180) {
                return null;
            }
        }

        return array_map(fn ($p) => [(float) $p[0], (float) $p[1]], array_values($titik));
    }
}
