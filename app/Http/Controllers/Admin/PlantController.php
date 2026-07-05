<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plant;
use Illuminate\Http\Request;

class PlantController extends Controller
{
    public function index()
    {
        $plants = Plant::orderBy('kode')->paginate(10);
        return view('admin.plants.index', compact('plants'));
    }

    public function create()
    {
        return view('admin.plants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:plants,kode',
            'nama' => 'required|string|max:255',
            'gambar' => 'nullable|string|max:500',
            'suhu_ideal' => 'required|string|max:50',
            'min_suhu' => 'required|numeric',
            'max_suhu' => 'required|numeric|gte:min_suhu',
            'kelembapan_ideal' => 'required|string|max:50',
            'min_kelembaban' => 'required|numeric',
            'max_kelembaban' => 'required|numeric|gte:min_kelembaban',
            'curah_hujan_ideal' => 'required|string|max:50',
            'musim' => 'required|string|max:100',
            'jenis_tanah' => 'required|string|max:100',
            'durasi_panen' => 'nullable|string|max:100',
            'status_cuaca_hujan' => 'nullable|string',
            'status_cuaca_panas' => 'nullable|string',
            'status_curah_hujan_tinggi' => 'nullable|string',
            'status_curah_hujan_rendah' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'keunggulan' => 'nullable|string',
            'budidaya_persiapan_lahan' => 'nullable|string',
            'budidaya_pemupukan' => 'nullable|string',
            'budidaya_irigasi' => 'nullable|string',
            'langkah_budidaya' => 'nullable|array',
            'langkah_budidaya.*.title' => 'nullable|string',
            'langkah_budidaya.*.content' => 'nullable|string',
            'tips_budidaya' => 'nullable|array',
            'tips_budidaya.*.icon' => 'nullable|string',
            'tips_budidaya.*.title' => 'nullable|string',
            'tips_budidaya.*.content' => 'nullable|string',
        ]);

        // Handle JSON fields - hanya parse jika ada data
        if (isset($validated['langkah_budidaya']) && is_array($validated['langkah_budidaya'])) {
            $validated['langkah_budidaya'] = $this->parseJsonSteps($validated['langkah_budidaya']);
        } else {
            $validated['langkah_budidaya'] = null;
        }

        if (isset($validated['tips_budidaya']) && is_array($validated['tips_budidaya'])) {
            $validated['tips_budidaya'] = $this->parseJsonTips($validated['tips_budidaya']);
        } else {
            $validated['tips_budidaya'] = null;
        }

        Plant::create($validated);

        return redirect()->route('admin.plants.index')
            ->with('success', 'Tanaman berhasil ditambahkan!');
    }

    public function show(Plant $plant)
    {
        return view('admin.plants.show', compact('plant'));
    }

    public function edit(Plant $plant)
    {
        return view('admin.plants.edit', compact('plant'));
    }

    public function update(Request $request, Plant $plant)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:plants,kode,' . $plant->id,
            'nama' => 'required|string|max:255',
            'gambar' => 'nullable|string|max:500',
            'suhu_ideal' => 'required|string|max:50',
            'min_suhu' => 'required|numeric',
            'max_suhu' => 'required|numeric|gte:min_suhu',
            'kelembapan_ideal' => 'required|string|max:50',
            'min_kelembaban' => 'required|numeric',
            'max_kelembaban' => 'required|numeric|gte:min_kelembaban',
            'curah_hujan_ideal' => 'required|string|max:50',
            'musim' => 'required|string|max:100',
            'jenis_tanah' => 'required|string|max:100',
            'durasi_panen' => 'nullable|string|max:100',
            'status_cuaca_hujan' => 'nullable|string',
            'status_cuaca_panas' => 'nullable|string',
            'status_curah_hujan_tinggi' => 'nullable|string',
            'status_curah_hujan_rendah' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'keunggulan' => 'nullable|string',
            'budidaya_persiapan_lahan' => 'nullable|string',
            'budidaya_pemupukan' => 'nullable|string',
            'budidaya_irigasi' => 'nullable|string',
            'langkah_budidaya' => 'nullable|array',
            'langkah_budidaya.*.title' => 'nullable|string',
            'langkah_budidaya.*.content' => 'nullable|string',
            'tips_budidaya' => 'nullable|array',
            'tips_budidaya.*.icon' => 'nullable|string',
            'tips_budidaya.*.title' => 'nullable|string',
            'tips_budidaya.*.content' => 'nullable|string',
        ]);

        // Handle JSON fields - hanya parse jika ada data
        if (isset($validated['langkah_budidaya']) && is_array($validated['langkah_budidaya'])) {
            $validated['langkah_budidaya'] = $this->parseJsonSteps($validated['langkah_budidaya']);
        } else {
            $validated['langkah_budidaya'] = $plant->langkah_budidaya; // Keep existing
        }

        if (isset($validated['tips_budidaya']) && is_array($validated['tips_budidaya'])) {
            $validated['tips_budidaya'] = $this->parseJsonTips($validated['tips_budidaya']);
        } else {
            $validated['tips_budidaya'] = $plant->tips_budidaya; // Keep existing
        }

        $plant->update($validated);

        return redirect()->route('admin.plants.index')
            ->with('success', 'Tanaman berhasil diupdate!');
    }

    public function destroy(Plant $plant)
    {
        $plant->delete();

        return redirect()->route('admin.plants.index')
            ->with('success', 'Tanaman berhasil dihapus!');
    }

    private function parseJsonSteps(array $steps): array
    {
        $result = [];
        foreach ($steps as $step) {
            if (!empty($step['title']) || !empty($step['content'])) {
                $result[] = [
                    'title' => $step['title'] ?? '',
                    'content' => $step['content'] ?? ''
                ];
            }
        }
        return $result;
    }

    private function parseJsonTips(array $tips): array
    {
        $result = [];
        foreach ($tips as $tip) {
            if (!empty($tip['title']) || !empty($tip['content'])) {
                $result[] = [
                    'icon' => $tip['icon'] ?? '💡',
                    'title' => $tip['title'] ?? '',
                    'content' => $tip['content'] ?? ''
                ];
            }
        }
        return $result;
    }
}