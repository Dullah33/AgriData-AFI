@extends('layouts.dashboard')

@section('title', $artikel->judul)

@section('content')

<div class="mb-4">
    <a href="{{ route('artikel.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali ke Artikel Pertanian</a>
</div>

<article class="bg-white rounded-xl shadow-sm overflow-hidden max-w-3xl">
    @if ($artikel->foto_sampul)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($artikel->foto_sampul) }}"
             alt="{{ $artikel->judul }}" class="w-full h-64 object-cover">
    @endif

    <div class="p-6">
        @if ($artikel->kategori)
            <span class="text-xs font-semibold text-blue-600 uppercase">{{ $artikel->kategori }}</span>
        @endif
        <h1 class="text-2xl font-bold text-gray-800 mt-1 mb-2">{{ $artikel->judul }}</h1>
        <p class="text-xs text-gray-400 mb-6">
            Oleh {{ $artikel->penulis->username ?? 'Admin' }} &middot; {{ $artikel->published_at?->format('d M Y') }}
        </p>

        <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-line">{{ $artikel->konten }}</div>
    </div>
</article>

@if ($terkait->isNotEmpty())
    <div class="max-w-3xl mt-8">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Artikel Terkait</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach ($terkait as $t)
                <a href="{{ route('artikel.show', $t) }}"
                   class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow">
                    <h3 class="font-semibold text-gray-800 text-sm line-clamp-2">{{ $t->judul }}</h3>
                    <p class="text-xs text-gray-400 mt-2">{{ $t->published_at?->format('d M Y') }}</p>
                </a>
            @endforeach
        </div>
    </div>
@endif

@endsection
