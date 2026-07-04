@extends('layouts.dashboard')

@section('title', 'Artikel Pertanian')

@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Artikel Pertanian</h2>

    <form method="GET" action="{{ route('artikel.index') }}" class="flex flex-wrap gap-3">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari judul artikel..."
               class="flex-1 min-w-[200px] border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <select name="kategori" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Kategori</option>
            @foreach ($kategoriList as $kat)
                <option value="{{ $kat }}" {{ request('kategori') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">Cari</button>
        @if (request('cari') || request('kategori'))
            <a href="{{ route('artikel.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:underline self-center">Reset</a>
        @endif
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse ($artikels as $artikel)
        <a href="{{ route('artikel.show', $artikel) }}"
           class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow flex flex-col">
            @if ($artikel->foto_sampul)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($artikel->foto_sampul) }}"
                     alt="{{ $artikel->judul }}" class="w-full h-40 object-cover">
            @else
                <div class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-300 text-3xl">🌱</div>
            @endif
            <div class="p-4 flex-1 flex flex-col">
                @if ($artikel->kategori)
                    <span class="text-xs font-semibold text-blue-600 uppercase mb-1">{{ $artikel->kategori }}</span>
                @endif
                <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">{{ $artikel->judul }}</h3>
                <p class="text-xs text-gray-400 mt-auto pt-2">
                    {{ $artikel->penulis->username ?? 'Admin' }} &middot; {{ $artikel->published_at?->format('d M Y') }}
                </p>
            </div>
        </a>
    @empty
        <div class="col-span-full bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
            Belum ada artikel yang dipublikasikan.
        </div>
    @endforelse
</div>

@if ($artikels->hasPages())
    <div class="mt-6">{{ $artikels->links() }}</div>
@endif

@endsection
