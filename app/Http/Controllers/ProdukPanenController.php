<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProdukPanenRequest;
use App\Http\Resources\ProdukPanenResource;
use App\Models\ProdukPanen;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

/**
 * API produk panen (/api/produk-panen, token Sanctum).
 * Produk yang tidak ada mengembalikan 404 JSON lewat ->missing() di routes/api.php.
 */
class ProdukPanenController extends Controller
{
    /**
     * GET /api/produk-panen?kategori=...&start_date=...&end_date=...
     */
    public function index(Request $request)
    {
        $filter = $request->validate([
            'kategori'   => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date', 'required_with:end_date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date', 'required_with:start_date'],
        ]);

        $produk = ProdukPanen::with('petani')
            ->terlihat()
            ->when($filter['kategori'] ?? null, fn ($q, $kategori) => $q->where('kategori', 'like', "%{$kategori}%"))
            ->when($filter['start_date'] ?? null, fn ($q) => $q->whereBetween('created_at', [
                Carbon::parse($filter['start_date'])->startOfDay(),
                Carbon::parse($filter['end_date'])->endOfDay(),
            ]))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return ProdukPanenResource::collection($produk);
    }

    /**
     * POST /api/produk-panen (khusus petani)
     */
    public function store(StoreProdukPanenRequest $request)
    {
        Gate::authorize('create', ProdukPanen::class);

        $produk = ProdukPanen::create($request->validated() + [
            'petani_id' => $request->user()->id,
            'status'    => 'tersedia',
        ]);

        return (new ProdukPanenResource($produk))
            ->additional(['message' => 'Produk panen berhasil ditambahkan'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/produk-panen/{id}
     */
    public function show(ProdukPanen $produkPanen)
    {
        abort_unless(in_array($produkPanen->status, ProdukPanen::STATUS_TERLIHAT, true), 404);

        return new ProdukPanenResource($produkPanen->load('petani'));
    }

    /**
     * PUT/PATCH /api/produk-panen/{id} (hanya pemilik)
     */
    public function update(StoreProdukPanenRequest $request, ProdukPanen $produkPanen)
    {
        Gate::authorize('update', $produkPanen);

        $produkPanen->update($request->validated());

        return (new ProdukPanenResource($produkPanen))
            ->additional(['message' => 'Produk panen berhasil diperbarui']);
    }

    /**
     * DELETE /api/produk-panen/{id} (hanya pemilik)
     *
     * Hapus lunak lewat status, sama seperti halaman petani: produk yang
     * pernah dibeli tetap dirujuk oleh tabel transaksi.
     */
    public function destroy(ProdukPanen $produkPanen)
    {
        Gate::authorize('delete', $produkPanen);

        $produkPanen->update(['status' => 'dihapus']);

        return response()->json(['message' => 'Produk panen berhasil dihapus']);
    }
}
