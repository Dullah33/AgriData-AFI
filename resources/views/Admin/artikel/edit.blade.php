@extends('layouts.dashboard')

@section('title', 'Edit Artikel')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.artikel.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
        <h2 class="text-lg font-semibold text-gray-800">Edit Artikel</h2>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.artikel.update', $artikel) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Artikel <span class="text-red-500">*</span></label>
            <input type="text" name="judul" value="{{ old('judul', $artikel->judul) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
            <select name="kategori" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih Kategori --</option>
                @foreach ($kategoriList as $kat)
                    <option value="{{ $kat }}" {{ old('kategori', $artikel->kategori) === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Isi Artikel <span class="text-red-500">*</span></label>
            <textarea name="konten" rows="12"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('konten', $artikel->konten) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Foto Sampul</label>
            @if ($artikel->foto_sampul)
                <div class="mb-2">
                    <img src="{{ Storage::url($artikel->foto_sampul) }}" alt="Foto Sampul"
                         class="h-32 w-auto rounded-lg object-cover border border-gray-200">
                    <p class="text-xs text-gray-400 mt-1">Foto saat ini. Upload baru untuk mengganti.</p>
                </div>
            @endif
            <input type="file" name="foto_sampul" accept="image/*"
                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 rounded-lg">
            <p class="mt-1 text-xs text-gray-400">Format JPG, PNG, WEBP. Maksimal 2MB.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status Publikasi <span class="text-red-500">*</span></label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio" name="status" value="draft" {{ old('status', $artikel->status) === 'draft' ? 'checked' : '' }}>
                    <span>Draft</span>
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio" name="status" value="published" {{ old('status', $artikel->status) === 'published' ? 'checked' : '' }}>
                    <span>Published</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.artikel.index') }}"
               class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
