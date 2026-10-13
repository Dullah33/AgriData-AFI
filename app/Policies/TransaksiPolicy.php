<?php

namespace App\Policies;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransaksiPolicy
{
    /**
     * Ulasan hanya untuk transaksi milik sendiri, yang sudah selesai, dan
     * belum pernah diulas. Sebelumnya tiga cek ini ditulis ulang di
     * UlasanController::create dan ::store.
     */
    public function ulas(User $user, Transaksi $transaksi): Response
    {
        if ($transaksi->user_id !== $user->id) {
            return Response::deny();
        }

        if ($transaksi->status_transaksi !== Transaksi::SELESAI) {
            return Response::deny('Ulasan hanya bisa diberikan setelah transaksi selesai.');
        }

        if ($transaksi->ulasan()->exists()) {
            return Response::deny('Anda sudah memberikan ulasan untuk transaksi ini.');
        }

        return Response::allow();
    }
}
