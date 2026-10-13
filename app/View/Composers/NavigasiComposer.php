<?php

namespace App\View\Composers;

use App\Models\KeranjangItem;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Angka badge di sidebar dan navbar (isi keranjang, pembayaran yang perlu
 * diverifikasi). Sebelumnya dihitung langsung di Blade, sehingga query
 * keranjang jalan dua kali di setiap halaman (sekali di sidebar, sekali
 * di navbar). Hasilnya disimpan di atribut request, jadi dihitung sekali
 * per request lalu dipakai kedua partial.
 */
class NavigasiComposer
{
    public function compose(View $view): void
    {
        $atribut = request()->attributes;

        if (! $atribut->has('navigasi.badge')) {
            $atribut->set('navigasi.badge', $this->hitung());
        }

        $view->with($atribut->get('navigasi.badge'));
    }

    private function hitung(): array
    {
        $user = Auth::user();

        return [
            'jumlahKeranjang' => $user?->isUser() ? KeranjangItem::where('user_id', $user->id)->count() : 0,
            'jumlahVerifikasi' => $user?->isAdmin() ? Pembayaran::status(Pembayaran::MENUNGGU_VERIFIKASI)->count() : 0,
        ];
    }
}
