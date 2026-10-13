<?php

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;

// Alasan wajib saat menolak pesanan (petani) atau bukti transfer (admin)
class AlasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alasan' => ['required', 'string', 'min:5', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'alasan.required' => 'Alasan harus diisi supaya pembeli tahu apa yang perlu dilakukan.',
            'alasan.min' => 'Alasan terlalu singkat.',
            'alasan.max' => 'Alasan maksimal 300 karakter.',
        ];
    }
}
