<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\UlasanRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UlasanController extends Controller
{
    // GET /ulasan/{transaksi}/create
    public function create(Transaksi $transaksi)
    {
        // Pastikan ini transaksi milik user yang login
        abort_if($transaksi->user_id !== Auth::id(), 403);
        // Hanya bisa beri ulasan kalau transaksi sudah selesai
        abort_if($transaksi->status_transaksi !== 'selesai', 403, 'Ulasan hanya bisa diberikan setelah transaksi selesai.');
        // Cegah ulasan ganda
        abort_if($transaksi->ulasan()->exists(), 403, 'Anda sudah memberikan ulasan untuk transaksi ini.');

        $transaksi->load('produk.petani');

        return view('user.ulasan.create', compact('transaksi'));
    }

    // POST /ulasan/{transaksi}
    public function store(Request $request, Transaksi $transaksi)
    {
        abort_if($transaksi->user_id !== Auth::id(), 403);
        abort_if($transaksi->status_transaksi !== 'selesai', 403);
        abort_if($transaksi->ulasan()->exists(), 403);

        $data = $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:500',
        ], [
            'rating.required' => 'Rating harus dipilih.',
            'rating.min'      => 'Rating minimal 1 bintang.',
            'rating.max'      => 'Rating maksimal 5 bintang.',
        ]);

        UlasanRating::create([
            'transaksi_id' => $transaksi->id,
            'rating'       => $data['rating'],
            'komentar'     => $data['komentar'] ?? null,
            'status'       => 'aktif',
        ]);

        return redirect()->route('user.pesanan')
            ->with('success', 'Ulasan berhasil dikirim, terima kasih!');
    }
}
