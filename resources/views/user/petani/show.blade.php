@extends('layouts.dashboard')

@section('title', 'Profil Petani')

@section('content')

<div class="mb-4">
    <a href="{{ route('user.petani.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali ke Profil Petani</a>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mb-6 max-w-2xl">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">{{ $petani->user->username ?? '-' }}</h2>
            <p class="text-sm text-gray-500">{{ $petani->nama_kelompok_tani ?? 'Tanpa kelompok tani' }}</p>
        </div>
        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Terverifikasi</span>
    </div>
    <dl class="grid grid-cols-2 gap-y-3 text-sm">
        <dt class="text-gray-500">Wilayah</dt>
        <dd class="text-gray-800">{{ $petani->wilayah->nama_wilayah ?? '-' }}</dd>

        <dt class="text-gray-500">Komoditas Utama</dt>
        <dd class="text-gray-800">{{ $petani->komoditas_utama ?? '-' }}</dd>
    </dl>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <h3 class="text-base font-semibold text-gray-800 mb-4">Produk yang Dijual</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @forelse ($produk as $p)
            <a href="{{ route('user.marketplace.show', $p) }}"
               class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow flex gap-3">
                @if ($p->foto_produk)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($p->foto_produk) }}"
                         class="w-16 h-16 object-cover rounded-lg" alt="{{ $p->nama_produk }}">
                @else
                    <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-gray-300">🌾</div>
                @endif
                <div>
                    <p class="font-medium text-gray-800 text-sm">{{ $p->nama_produk }}</p>
                    <p class="text-sm text-green-700 font-semibold">Rp {{ number_format($p->harga, 0, ',', '.') }}/{{ $p->satuan }}</p>
                </div>
            </a>
        @empty
            <p class="col-span-full text-center text-gray-400 py-6">Belum ada produk yang dijual petani ini.</p>
        @endforelse
    </div>
</div>

@endsection