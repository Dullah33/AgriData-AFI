@extends('layouts.dashboard')
@section('title', $produk->nama_produk)
@section('content')

<div class="max-w-2xl">
    <a href="{{ route('user.marketplace') }}"
       class="text-sm text-blue-600 hover:underline mb-4 block">← Kembali ke Marketplace</a>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Foto Produk --}}
        @if ($produk->foto_produk)
            <img src="{{ Storage::url($produk->foto_produk) }}" class="w-full h-64 object-cover">
        @else
            <div class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-400 text-sm">
                Tidak ada foto
            </div>
        @endif

        <div class="p-6">
            {{-- Info Produk --}}
            <span class="text-xs font-semibold text-blue-600 uppercase tracking-wide">{{ $produk->kategori }}</span>
            <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $produk->nama_produk }}</h2>
            <p class="text-sm text-gray-500 mt-1">Dijual oleh: <span class="font-medium text-gray-700">{{ $produk->petani->username }}</span></p>

            <div class="flex items-center gap-6 mt-4 pb-4 border-b border-gray-100">
                <div>
                    <span class="text-3xl font-bold text-green-700">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                    <span class="text-sm text-gray-400">/{{ $produk->satuan }}</span>
                </div>
                <div class="text-sm text-gray-500">
                    Stok: <strong class="text-gray-800">{{ $produk->stok }} {{ $produk->satuan }}</strong>
                </div>
            </div>

            <p class="mt-4 text-gray-600 text-sm leading-relaxed">{{ $produk->deskripsi }}</p>

            {{-- Form Beli --}}
            <div class="mt-6 bg-blue-50 border border-blue-100 rounded-xl p-5">
                <h3 class="text-base font-semibold text-gray-800 mb-4">🛒 Form Pemesanan</h3>

                @if ($errors->any())
                    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('user.marketplace.beli', $produk) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jumlah ({{ $produk->satuan }}) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="jumlah" value="{{ old('jumlah', 1) }}"
                               min="1" max="{{ $produk->stok }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <p class="text-xs text-gray-400 mt-1">Maksimal {{ $produk->stok }} {{ $produk->satuan }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Alamat Pengiriman <span class="text-red-500">*</span>
                        </label>
                        <textarea name="alamat_pengiriman" rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                  placeholder="Masukkan alamat lengkap (jalan, RT/RW, desa, kecamatan, kota)...">{{ old('alamat_pengiriman') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan untuk Petani (opsional)</label>
                        <input type="text" name="catatan" value="{{ old('catatan') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                               placeholder="Misal: tolong pilihkan yang segar...">
                    </div>

                    {{-- Tombol Beli yang Jelas --}}
                    <button type="submit"
                            class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-bold text-base rounded-lg border border-green-700 shadow-sm">
                        🛒 Pesan Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection