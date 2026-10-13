<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlantRequest;
use App\Models\Plant;

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

    public function store(PlantRequest $request)
    {
        Plant::create($request->dataTanaman());

        return redirect()->route('admin.plants.index')
            ->with('success', 'Tanaman berhasil ditambahkan!');
    }

    public function edit(Plant $plant)
    {
        return view('admin.plants.edit', compact('plant'));
    }

    public function update(PlantRequest $request, Plant $plant)
    {
        $plant->update($request->dataTanaman());

        return redirect()->route('admin.plants.index')
            ->with('success', 'Tanaman berhasil diupdate!');
    }

    public function destroy(Plant $plant)
    {
        $plant->delete();

        return redirect()->route('admin.plants.index')
            ->with('success', 'Tanaman berhasil dihapus!');
    }
}
