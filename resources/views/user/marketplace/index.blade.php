@extends('layouts.dashboard')
@section('title', 'Marketplace Hasil Panen')
@section('content')

@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Marketplace Hasil Panen</h2>

    {{-- Filter & Cari --}}
    <form method="GET" action="{{ route('user.marketplace') }}" class="flex gap-3 mb-6">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari produk..."
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm flex-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="kategori" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Kategori</option>
            @foreach ($kategoris as $k)
                <option value="{{ $k }}" {{ request('kategori') === $k ? 'selected' : '' }}>{{ $k }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg font-semibold">Cari</button>
        <a href="{{ route('user.marketplace') }}" class="px-4 py-2 text-sm text-gray-500 hover:underline self-center">Reset</a>
    </form>

    {{-- Grid Produk --}}
    @if ($produks->isEmpty())
        <p class="text-center text-gray-400 py-8">Tidak ada produk yang tersedia saat ini.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($produks as $p)
                <div class="border border-gray-100 rounded-xl overflow-hidden hover:shadow-md transition-shadow">
                    @if ($p->foto_produk)
                        <img src="{{ Storage::url($p->foto_produk) }}" class="w-full h-40 object-cover">
                    @else
                        <div class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-300 text-sm">Tidak ada foto</div>
                    @endif
                    <div class="p-4">
                        <span class="text-xs text-blue-600 font-semibold">{{ $p->kategori }}</span>
                        <h3 class="font-semibold text-gray-800 mt-1">{{ $p->nama_produk }}</h3>
                        <p class="text-xs text-gray-500 mt-1">Oleh: {{ $p->petani->username }}</p>
                        <div class="flex items-center justify-between mt-3">
                            <div>
                                <span class="text-lg font-bold text-green-700">Rp {{ number_format($p->harga, 0, ',', '.') }}</span>
                                <span class="text-xs text-gray-400">/{{ $p->satuan }}</span>
                            </div>
                            <span class="text-xs text-gray-500">Stok: {{ $p->stok }}</span>
                        </div>
                        <a href="{{ route('user.marketplace.show', $p) }}"
                           class="mt-3 block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        @if ($produks->hasPages()) <div class="mt-6">{{ $produks->links() }}</div> @endif
    @endif
</div>
@endsection
