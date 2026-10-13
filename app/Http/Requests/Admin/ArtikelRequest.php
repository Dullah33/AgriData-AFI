<?php

namespace App\Http\Requests\Admin;

use App\Models\Artikel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArtikelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul'       => ['required', 'string', 'max:200'],
            // Hanya kategori yang terdaftar (sebelumnya teks bebas apa saja)
            'kategori'    => ['required', Rule::in(Artikel::KATEGORI)],
            'konten'      => ['required', 'string'],
            'foto_sampul' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status'      => ['required', 'in:draft,published'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required'    => 'Judul artikel harus diisi.',
            'kategori.required' => 'Kategori harus dipilih.',
            'kategori.in'       => 'Kategori tidak dikenal.',
            'konten.required'   => 'Isi artikel harus diisi.',
            'foto_sampul.image' => 'File harus berupa gambar.',
            'foto_sampul.max'   => 'Ukuran foto maksimal 2MB.',
            'status.required'   => 'Status publikasi harus dipilih.',
        ];
    }
}
