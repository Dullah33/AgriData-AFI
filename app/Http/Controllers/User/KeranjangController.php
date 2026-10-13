<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\TambahKeranjangRequest;
use App\Http\Requests\Marketplace\UbahKeranjangRequest;
use App\Models\KeranjangItem;
use App\Models\ProdukPanen;
use Illuminate\Support\Facades\Auth;

class KeranjangController extends Controller
{
    // GET /keranjang
    public function index()
    {
        $items = KeranjangItem::with('produk.petani')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        // Dikelompokkan per petani karena tiap petani mengirim sendiri-sendiri
        $perPetani = $items->groupBy(fn (KeranjangItem $item) => $item->produk?->petani?->username ?? 'Lainnya');
        $total = $items->sum(fn (KeranjangItem $item) => $item->subtotal());
        $adaMasalah = $items->contains(fn (KeranjangItem $item) => $item->masalahStok() !== null);

        return view('user.keranjang.index', compact('items', 'perPetani', 'total', 'adaMasalah'));
    }

    // POST /marketplace/{produk}/keranjang  (tombol "Tambah ke keranjang" & "Beli sekarang")
    public function store(TambahKeranjangRequest $request, ProdukPanen $produk)
    {
        $item = $request->itemDiKeranjang();
        $jumlah = (int) $request->validated('jumlah');

        if ($item) {
            $item->increment('jumlah', $jumlah);
        } else {
            KeranjangItem::create(['user_id' => Auth::id(), 'produk_id' => $produk->id, 'jumlah' => $jumlah]);
        }

        if ($request->input('aksi') === 'beli') {
            return redirect()->route('user.checkout.create');
        }

        return back()->with('success', "{$produk->nama_produk} ditambahkan ke keranjang.");
    }

    // PATCH /keranjang/{item}
    public function update(UbahKeranjangRequest $request, KeranjangItem $item)
    {
        $item->update(['jumlah' => (int) $request->validated('jumlah')]);

        return back()->with('success', 'Jumlah diperbarui.');
    }

    // DELETE /keranjang/{item}
    public function destroy(KeranjangItem $item)
    {
        abort_unless($item->user_id === Auth::id(), 404);

        $item->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
