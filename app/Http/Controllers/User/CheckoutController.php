<?php

namespace App\Http\Controllers\User;

use App\Exceptions\KeranjangKosongException;
use App\Exceptions\StokTidakCukupException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\CheckoutRequest;
use App\Models\KeranjangItem;
use App\Models\Transaksi;
use App\Services\Marketplace\CheckoutService;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    // GET /checkout
    public function create()
    {
        $items = KeranjangItem::with('produk.petani')->where('user_id', Auth::id())->get();

        if ($items->isEmpty()) {
            return redirect()->route('user.keranjang.index')->with('error', 'Keranjang Anda masih kosong.');
        }

        if ($items->contains(fn (KeranjangItem $item) => $item->masalahStok() !== null)) {
            return redirect()->route('user.keranjang.index')
                ->with('error', 'Beberapa produk di keranjang stoknya berubah. Sesuaikan jumlahnya dulu sebelum checkout.');
        }

        $total = $items->sum(fn (KeranjangItem $item) => $item->subtotal());
        $metodeTersedia = CheckoutRequest::metodeTersedia();

        // Isi otomatis alamat dari pesanan terakhir
        $alamatTerakhir = Transaksi::where('user_id', Auth::id())->whereNotNull('alamat_pengiriman')->latest()->value('alamat_pengiriman');

        return view('user.checkout.create', compact('items', 'total', 'metodeTersedia', 'alamatTerakhir'));
    }

    // POST /checkout
    public function store(CheckoutRequest $request, CheckoutService $checkout)
    {
        try {
            $pembayaran = $checkout->checkout($request->user(), $request->validated());
        } catch (StokTidakCukupException|KeranjangKosongException $e) {
            // Seluruh checkout sudah di-rollback; tidak ada stok yang berubah
            return redirect()->route('user.keranjang.index')->with('error', $e->getMessage());
        }

        return redirect()->route('user.pembayaran.show', $pembayaran)
            ->with('success', 'Pesanan dibuat. Selesaikan pembayaran sebelum batas waktu.');
    }
}
