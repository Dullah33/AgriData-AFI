<?php

namespace App\Http\Controllers;

use App\Models\ProdukPanen;
use App\Http\Requests\StoreProdukPanenRequest;
use App\Http\Resources\ProdukPanenResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProdukPanenController extends Controller
{
    /**
     * GET /api/produk-panen
     * Mendukung filter ?kategori=... dan rentang tanggal ?start_date=...&end_date=...
     * serta pagination otomatis (10 data per halaman).
     */
    public function index(Request $request)
    {
        $query = ProdukPanen::query();

        if ($request->has('kategori')) {
            $query->where('kategori', 'like', '%' . $request->kategori . '%');
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $produk = $query->paginate(10);

        return ProdukPanenResource::collection($produk);
    }

    /**
     * POST /api/produk-panen
     */
    public function store(StoreProdukPanenRequest $request)
    {
        $produk = ProdukPanen::create($request->validated() + [
            'petani_id' => $request->user()->id,
        ]);

        return (new ProdukPanenResource($produk))
            ->additional(['message' => 'Produk panen berhasil ditambahkan'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/produk-panen/{id}
     */
    public function show(int $id)
    {
        try {
            $produk = ProdukPanen::findOrFail($id);
            return new ProdukPanenResource($produk);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error'   => 'Resource tidak ditemukan',
                'message' => 'Produk panen dengan ID ' . $id . ' tidak ada di sistem.',
            ], 404);
        }
    }

    /**
     * PUT/PATCH /api/produk-panen/{id}
     */
    public function update(StoreProdukPanenRequest $request, int $id)
    {
        try {
            $produk = ProdukPanen::findOrFail($id);
            $produk->update($request->validated());

            return (new ProdukPanenResource($produk))
                ->additional(['message' => 'Produk panen berhasil diperbarui']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error'   => 'Resource tidak ditemukan',
                'message' => 'Produk panen dengan ID ' . $id . ' tidak ada di sistem.',
            ], 404);
        }
    }

    /**
     * DELETE /api/produk-panen/{id}
     */
    public function destroy(int $id)
    {
        try {
            $produk = ProdukPanen::findOrFail($id);
            $produk->delete();

            return response()->json([
                'message' => 'Produk panen berhasil dihapus',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error'   => 'Resource tidak ditemukan',
                'message' => 'Produk panen dengan ID ' . $id . ' tidak ada di sistem.',
            ], 404);
        }
    }
}
