@extends('layouts.dashboard')
@section('title', 'Tambah Produk Panen')
@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('petani.produk.index') }}"
           class="text-sm text-blue-600 hover:underline">← Kembali</a>
        <h2 class="text-lg font-semibold text-gray-800">Tambah Produk Baru</h2>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('petani.produk.store') }}"
          enctype="multipart/form-data" class="space-y-4">
        @csrf

        {{-- Nama Produk --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
            <input type="text" name="nama_produk" value="{{ old('nama_produk') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Contoh: Cabai Merah Keriting">
        </div>

        {{-- Kategori & Satuan --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                <select name="kategori"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($kategoriList as $k)
                        <option value="{{ $k }}" {{ old('kategori') === $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                <select name="satuan"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach (['kg','ton','ikat','buah','karung','liter'] as $s)
                        <option value="{{ $s }}" {{ old('satuan','kg') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Harga & Stok --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Harga per Satuan (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="harga" value="{{ old('harga') }}" min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Contoh: 15000">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stok Tersedia <span class="text-red-500">*</span></label>
                <input type="number" name="stok" value="{{ old('stok') }}" min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Contoh: 100">
            </div>
        </div>

        {{-- Deskripsi --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Produk <span class="text-red-500">*</span></label>
            <textarea name="deskripsi" rows="4"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Ceritakan kondisi, kualitas, dan keunggulan produk Anda...">{{ old('deskripsi') }}</textarea>
        </div>

        {{-- Foto Produk (dengan kotak dropzone) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Foto Produk</label>
            <label for="foto_produk"
                   class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 hover:border-blue-400 transition">
                <div class="flex flex-col items-center justify-center text-center px-4">
                    <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-gray-500 font-medium">Klik untuk pilih foto</p>
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP — Maks. 2MB</p>
                </div>
                <input id="foto_produk" type="file" name="foto_produk" accept="image/*" class="hidden"
                       onchange="document.getElementById('nama-file').textContent = this.files[0]?.name ?? ''">
            </label>
            <p id="nama-file" class="mt-1 text-xs text-blue-600"></p>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg border border-blue-700">
                💾 Simpan Produk
            </button>
            <a href="{{ route('petani.produk.index') }}"
               class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection