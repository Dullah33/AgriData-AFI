@extends('layouts.dashboard')

@section('title', 'Detail Petani')

@section('content')

@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Detail Profil Petani</h2>
        <a href="{{ route('admin.petani.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali</a>
    </div>

    <dl class="grid grid-cols-3 gap-y-4 text-sm">
        <dt class="text-gray-500">Username</dt>
        <dd class="col-span-2 text-gray-800 font-medium">{{ $petani->user->username ?? '-' }}</dd>

        <dt class="text-gray-500">Email</dt>
        <dd class="col-span-2 text-gray-800">{{ $petani->user->email ?? '-' }}</dd>

        <dt class="text-gray-500">NIK</dt>
        <dd class="col-span-2 text-gray-800">{{ $petani->nik ?? '-' }}</dd>

        <dt class="text-gray-500">Alamat</dt>
        <dd class="col-span-2 text-gray-800">{{ $petani->alamat ?? '-' }}</dd>

        <dt class="text-gray-500">Wilayah</dt>
        <dd class="col-span-2 text-gray-800">{{ $petani->wilayah->nama_wilayah ?? '-' }}</dd>

        <dt class="text-gray-500">Kelompok Tani</dt>
        <dd class="col-span-2 text-gray-800">{{ $petani->nama_kelompok_tani ?? '-' }}</dd>

        <dt class="text-gray-500">Komoditas Utama</dt>
        <dd class="col-span-2 text-gray-800">{{ $petani->komoditas_utama ?? '-' }}</dd>

        <dt class="text-gray-500">Luas Lahan</dt>
        <dd class="col-span-2 text-gray-800">{{ $petani->luas_lahan }} ha</dd>

        <dt class="text-gray-500">Foto KTP</dt>
        <dd class="col-span-2">
            @if ($petani->foto_ktp)
                <a href="{{ \Illuminate\Support\Facades\Storage::url($petani->foto_ktp) }}" target="_blank" class="text-blue-600 hover:underline">Lihat foto</a>
            @else
                <span class="text-gray-400">Belum diunggah</span>
            @endif
        </dd>

        <dt class="text-gray-500">Status</dt>
        <dd class="col-span-2">
            @if ($petani->isTerverifikasi())
                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">
                    Terverifikasi oleh {{ $petani->verifikator->username ?? '-' }}
                </span>
            @else
                <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">Belum Diverifikasi</span>
            @endif
        </dd>
    </dl>

    <div class="mt-6 flex gap-3">
        @if (! $petani->isTerverifikasi())
            <form method="POST" action="{{ route('admin.petani.verifikasi', $petani) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
                    Verifikasi Akun Ini
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.petani.batalkan-verifikasi', $petani) }}"
                  onsubmit="return confirm('Batalkan verifikasi petani ini?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-semibold rounded-lg">
                    Batalkan Verifikasi
                </button>
            </form>
        @endif
    </div>
</div>

@endsection
