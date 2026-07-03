<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use Illuminate\Http\Request;

class BudidayaController extends Controller
{
    /**
     * Tampilkan halaman list semua tanaman
     */
    public function index()
    {
        $plants = Plant::select('id', 'nama', 'gambar', 'deskripsi', 'musim', 'durasi_panen')->get();
        
        return view('budidaya.index', compact('plants'));
    }

    /**
     * Tampilkan halaman detail budidaya untuk satu tanaman
     */
    public function show($id)
    {
        $plant = Plant::with(['cuaca', 'penyakit'])->findOrFail($id);
        
        return view('budidaya.show', compact('plant'));
    }

    /**
     * API: Get data budidaya untuk AJAX loading
     */
    public function apiShow($id)
    {
        $plant = Plant::with(['cuaca', 'penyakit'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $plant
        ]);
    }
}