<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi tambah & ubah data tanaman (ensiklopedia budidaya). Sebelumnya
 * aturan yang sama ditulis dua kali di PlantController::store/update.
 */
class PlantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $plant = $this->route('plant');

        return [
            'kode' => ['required', 'string', 'max:10', Rule::unique('plants', 'kode')->ignore($plant?->id)],
            // Kolom nama unik di database; tanpa aturan ini duplikat berakhir error 500
            'nama' => ['required', 'string', 'max:255', Rule::unique('plants', 'nama')->ignore($plant?->id)],
            // Dipakai sebagai src gambar di halaman (termasuk lewat JavaScript),
            // jadi hanya URL http(s) atau path file tanpa spasi/kutip/tanda < >.
            'gambar' => ['nullable', 'string', 'max:500', 'regex:/^(https?:\/\/[^\s"\'<>]+|[A-Za-z0-9._\-\/]+)$/'],
            'suhu_ideal' => ['required', 'string', 'max:50'],
            'min_suhu' => ['required', 'numeric'],
            'max_suhu' => ['required', 'numeric', 'gte:min_suhu'],
            'kelembapan_ideal' => ['required', 'string', 'max:50'],
            'min_kelembaban' => ['required', 'numeric'],
            'max_kelembaban' => ['required', 'numeric', 'gte:min_kelembaban'],
            'curah_hujan_ideal' => ['required', 'string', 'max:50'],
            'musim' => ['required', 'string', 'max:100'],
            'jenis_tanah' => ['required', 'string', 'max:100'],
            'durasi_panen' => ['nullable', 'string', 'max:100'],
            'status_cuaca_hujan' => ['nullable', 'string'],
            'status_cuaca_panas' => ['nullable', 'string'],
            'status_curah_hujan_tinggi' => ['nullable', 'string'],
            'status_curah_hujan_rendah' => ['nullable', 'string'],
            'deskripsi' => ['nullable', 'string'],
            'keunggulan' => ['nullable', 'string'],
            'budidaya_persiapan_lahan' => ['nullable', 'string'],
            'budidaya_pemupukan' => ['nullable', 'string'],
            'budidaya_irigasi' => ['nullable', 'string'],
            'langkah_budidaya' => ['nullable', 'array'],
            'langkah_budidaya.*.title' => ['nullable', 'string', 'max:255'],
            'langkah_budidaya.*.content' => ['nullable', 'string'],
            'tips_budidaya' => ['nullable', 'array'],
            'tips_budidaya.*.icon' => ['nullable', 'string', 'max:16'],
            'tips_budidaya.*.title' => ['nullable', 'string', 'max:255'],
            'tips_budidaya.*.content' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.unique' => 'Tanaman dengan nama ini sudah ada.',
            'gambar.regex' => 'Gambar harus berupa URL http(s) atau path file, tanpa spasi.',
        ];
    }

    /**
     * Data siap simpan. Baris langkah/tips yang judul dan isinya kosong
     * dibuang. Kalau field tidak dikirim sama sekali, dibiarkan tidak ada:
     * saat tambah menjadi null, saat ubah nilai lama dipertahankan.
     */
    public function dataTanaman(): array
    {
        $data = $this->validated();

        if (array_key_exists('langkah_budidaya', $data)) {
            $data['langkah_budidaya'] = $this->rapikan($data['langkah_budidaya'], ['title' => '', 'content' => '']);
        }

        if (array_key_exists('tips_budidaya', $data)) {
            $data['tips_budidaya'] = $this->rapikan($data['tips_budidaya'], ['icon' => '💡', 'title' => '', 'content' => '']);
        }

        return $data;
    }

    private function rapikan(?array $baris, array $bawaan): ?array
    {
        if ($baris === null) {
            return null;
        }

        return collect($baris)
            ->filter(fn ($b) => filled($b['title'] ?? null) || filled($b['content'] ?? null))
            ->map(fn ($b) => array_merge($bawaan, array_filter(array_intersect_key($b, $bawaan), 'filled')))
            ->values()
            ->all();
    }
}
