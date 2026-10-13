<?php

namespace App\Http\Requests\Marketplace;

use App\Models\KeranjangItem;
use App\Models\Pembayaran;
use App\Services\Marketplace\MidtransGateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi checkout. Selain data pengiriman, setiap item di keranjang
 * dicek terhadap stok terkini. Pengecekan ini untuk pesan yang ramah;
 * jaminan sebenarnya ada di CheckoutService yang mengunci baris produk
 * di dalam DB::transaction (antisipasi pembeli lain checkout bersamaan).
 */
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alamat_pengiriman' => ['required', 'string', 'min:10', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:300'],
            'metode' => ['required', Rule::in(static::metodeTersedia())],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $items = KeranjangItem::with('produk')->where('user_id', $this->user()->id)->get();

                if ($items->isEmpty()) {
                    $validator->errors()->add('keranjang', 'Keranjang Anda masih kosong.');

                    return;
                }

                foreach ($items as $item) {
                    if ($masalah = $item->masalahStok()) {
                        $validator->errors()->add('keranjang', ($item->produk->nama_produk ?? 'Produk').': '.$masalah);
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'alamat_pengiriman.required' => 'Alamat pengiriman harus diisi.',
            'alamat_pengiriman.min' => 'Alamat pengiriman terlalu singkat. Tulis jalan, desa, kecamatan, dan kota.',
            'alamat_pengiriman.max' => 'Alamat pengiriman maksimal 500 karakter.',
            'catatan.max' => 'Catatan maksimal 300 karakter.',
            'metode.required' => 'Pilih metode pembayaran.',
            'metode.in' => 'Metode pembayaran tidak tersedia.',
        ];
    }

    /**
     * Metode yang bisa dipilih saat ini. Transfer manual butuh nomor
     * rekening di konfigurasi, Midtrans butuh server key atau mode simulasi.
     */
    public static function metodeTersedia(): array
    {
        return array_values(array_filter([
            filled(config('marketplace.rekening.nomor')) ? Pembayaran::METODE_TRANSFER : null,
            app(MidtransGateway::class)->tersedia() ? Pembayaran::METODE_MIDTRANS : null,
        ]));
    }
}
