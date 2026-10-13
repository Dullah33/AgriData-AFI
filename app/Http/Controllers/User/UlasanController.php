<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UlasanController extends Controller
{
    // GET /ulasan/{transaksi}/create
    public function create(Transaksi $transaksi)
    {
        Gate::authorize('ulas', $transaksi);

        return view('user.ulasan.create', [
            'transaksi' => $transaksi->load('produk.petani'),
        ]);
    }

    // POST /ulasan/{transaksi}
    public function store(Request $request, Transaksi $transaksi)
    {
        Gate::authorize('ulas', $transaksi);

        $data = $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:500',
        ], [
            'rating.required' => 'Rating harus dipilih.',
            'rating.min'      => 'Rating minimal 1 bintang.',
            'rating.max'      => 'Rating maksimal 5 bintang.',
        ]);

        $transaksi->ulasan()->create($data + ['status' => 'aktif']);

        return redirect()->route('user.riwayat-ulasan')
            ->with('success', 'Ulasan berhasil dikirim, terima kasih!');
    }
}
