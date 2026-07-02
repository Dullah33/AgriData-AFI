@extends('layouts.dashboard')
@section('title', 'Listing Hasil Panen Saya')
@section('content')

@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Produk Hasil Panen Saya</h2>
        <a href="{{ route('petani.produk.create') }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
            + Tambah Produk
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3">Stok</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($produk as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($item->foto_produk)
                                    <img src="{{ Storage::url($item->foto_produk) }}"
                                         class="w-10 h-10 rounded-lg object-cover border border-gray-200">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs border border-gray-200">
                                        Foto
                                    </div>
                                @endif
                                <span class="font-medium text-gray-800">{{ $item->nama_produk }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $item->kategori ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-800">
                            Rp {{ number_format($item->harga, 0, ',', '.') }}/{{ $item->satuan }}
                        </td>
                        <td class="px-4 py-3 text-gray-800">{{ $item->stok }} {{ $item->satuan }}</td>
                        <td class="px-4 py-3">
                            @if ($item->status === 'tersedia')
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Tersedia</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">Habis</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                {{-- Tombol Edit --}}
                                <a href="{{ route('petani.produk.edit', $item) }}"
                                   class="inline-block px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-100 border border-blue-300 rounded-lg hover:bg-blue-200">
                                    ✏️ Edit
                                </a>
                                {{-- Tombol Hapus --}}
                                <form method="POST" action="{{ route('petani.produk.destroy', $item) }}"
                                      onsubmit="return confirm('Hapus produk ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-100 border border-red-300 rounded-lg hover:bg-red-200">
                                        🗑️ Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                            Belum ada produk. Klik "+ Tambah Produk" untuk mulai berjualan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($produk->hasPages())
        <div class="mt-4">{{ $produk->links() }}</div>
    @endif
</div>
@endsection