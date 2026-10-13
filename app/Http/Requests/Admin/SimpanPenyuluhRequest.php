<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi tambah (POST) dan ubah (PUT) data Penyuluh oleh admin.
 * Saat ubah, akun user tidak diganti, hanya profil penyuluhnya.
 */
class SimpanPenyuluhRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $penyuluh = $this->route('penyuluh');

        $profil = [
            'nip'            => ['nullable', 'string', 'max:30', Rule::unique('extension_officers', 'nip')->ignore($penyuluh?->id)],
            'wilayah_binaan' => ['required', 'string', 'max:150'],
            'phone'          => ['nullable', 'string', 'max:20'],
        ];

        if ($penyuluh) {
            return $profil + ['status' => ['required', 'in:aktif,nonaktif']];
        }

        return $profil + [
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required'       => 'Username harus diisi.',
            'username.unique'         => 'Username sudah digunakan.',
            'email.required'          => 'Email harus diisi.',
            'email.unique'            => 'Email sudah terdaftar.',
            'password.required'       => 'Password harus diisi.',
            'password.min'            => 'Password minimal 8 karakter.',
            'password.confirmed'      => 'Konfirmasi password tidak cocok.',
            'nip.unique'              => 'NIP sudah terdaftar.',
            'wilayah_binaan.required' => 'Wilayah binaan harus diisi.',
        ];
    }
}
