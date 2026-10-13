<?php

namespace App\Http\Requests\Marketplace;

use App\Models\Pembayaran;
use Illuminate\Foundation\Http\FormRequest;

class UnggahBuktiTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->pembayaran()->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'bukti_transfer' => [
                'bail',
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'mimetypes:image/jpeg,image/png,image/webp,application/pdf',
                'extensions:jpg,jpeg,png,webp,pdf',
                'max:'.config('marketplace.bukti_maks_kb', 4096),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'bukti_transfer.required' => 'Pilih foto atau file bukti transfer.',
            'bukti_transfer.uploaded' => 'Bukti transfer gagal diunggah. Pastikan ukurannya tidak lebih dari 4 MB.',
            'bukti_transfer.mimes' => 'Bukti transfer harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'bukti_transfer.mimetypes' => 'Bukti transfer harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'bukti_transfer.extensions' => 'Bukti transfer harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'bukti_transfer.max' => 'Ukuran bukti transfer maksimal 4 MB.',
        ];
    }

    public function pembayaran(): Pembayaran
    {
        return $this->route('pembayaran');
    }
}
