@extends('layouts.dashboard')

@section('title', 'Profil Petani')

@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Profil Petani</h2>
    <form method="GET" action="{{ route('user.petani.index') }}" class="flex gap-3">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama petani..."
               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">Cari</button>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse ($petanis as $petani)
        <a href="{{ route('user.petani.show', $petani) }}"
           class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow">
            <h3 class="font-semibold text-gray-800">{{ $petani->user->username ?? '-' }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $petani->nama_kelompok_tani ?? 'Tanpa kelompok tani' }}</p>
            <div class="flex items-center gap-2 mt-3 text-xs text-gray-400">
                <span>📍 {{ $petani->wilayah->nama_wilayah ?? '-' }}</span>
            </div>
            @if ($petani->komoditas_utama)
                <span class="inline-block mt-3 px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">
                    {{ $petani->komoditas_utama }}
                </span>
            @endif
        </a>
    @empty
        <div class="col-span-full bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
            Belum ada profil petani yang terverifikasi.
        </div>
    @endforelse
</div>

@if ($petanis->hasPages())
    <div class="mt-6">{{ $petanis->links() }}</div>
@endif

@endsection