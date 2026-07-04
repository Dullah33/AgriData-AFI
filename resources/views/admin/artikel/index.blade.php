@extends('layouts.dashboard')

@section('title', 'Manajemen Artikel')

@section('content')

{{-- Flash message --}}
@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Daftar Artikel Pertanian</h2>
        <a href="{{ route('admin.artikel.create') }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
            + Tambah Artikel
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.artikel.index') }}" class="flex gap-3 mb-6">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
        </select>
        <select name="kategori" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Kategori</option>
            @foreach ($kategoriList as $kat)
                <option value="{{ $kat }}" {{ request('kategori') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-sm rounded-lg">Filter</button>
        <a href="{{ route('admin.artikel.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:underline self-center">Reset</a>
    </form>

    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($artikels as $artikel)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800 max-w-xs truncate">
                            {{ $artikel->judul }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $artikel->kategori ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($artikel->status === 'published')
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Published</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $artikel->published_at?->format('d M Y') ?? $artikel->created_at->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('admin.artikel.edit', $artikel) }}"
                               class="px-3 py-1 text-xs bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg font-semibold">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.artikel.destroy', $artikel) }}"
                                  onsubmit="return confirm('Hapus artikel ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1 text-xs bg-red-50 text-red-700 hover:bg-red-100 rounded-lg font-semibold">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                            Belum ada artikel. Klik "Tambah Artikel" untuk membuat yang pertama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($artikels->hasPages())
        <div class="mt-4">{{ $artikels->links() }}</div>
    @endif
</div>

@endsection
