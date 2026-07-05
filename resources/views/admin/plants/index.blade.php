@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Manajemen Tanaman</h1>
                <p class="text-gray-600 mt-1">Kelola data tanaman untuk sistem prediksi cuaca</p>
            </div>
            <a href="{{ route('admin.plants.create') }}" 
               class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Tambah Tanaman
            </a>
        </div>

        <!-- Success Message -->
        @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
            <p class="text-green-700 font-semibold">{{ session('success') }}</p>
        </div>
        @endif

        <!-- Plants Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Kode</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Musim</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Suhu Ideal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($plants as $plant)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $plant->kode }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $plant->nama }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $plant->musim }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $plant->suhu_ideal }}</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.plants.edit', $plant->id) }}" 
                                   class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition text-xs font-semibold">
                                    Edit
                                </a>
                                <form action="{{ route('admin.plants.destroy', $plant->id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Yakin ingin menghapus tanaman ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-xs font-semibold">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            Belum ada data tanaman. Klik "Tambah Tanaman" untuk menambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($plants->hasPages())
        <div class="mt-6">
            {{ $plants->links() }}
        </div>
        @endif

    </div>
</div>
@endsection