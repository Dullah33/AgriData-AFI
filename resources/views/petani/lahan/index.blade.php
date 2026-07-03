@extends('layouts.dashboard')

@section('title', 'Profil & Lahan Saya')

@section('content')

@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="space-y-4">

    {{-- Kartu Profil Petani --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Profil Saya</h2>
            @if ($petaniProfile->isTerverifikasi())
                <span class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">✓ Terverifikasi</span>
            @else
                <span class="px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">Menunggu Verifikasi Admin</span>
            @endif
        </div>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">NIK</p>
                <p class="font-medium text-gray-800">{{ $petaniProfile->nik ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Komoditas Utama</p>
                <p class="font-medium text-gray-800">{{ $petaniProfile->komoditas_utama ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Kelompok Tani</p>
                <p class="font-medium text-gray-800">{{ $petaniProfile->nama_kelompok_tani ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Total Luas Lahan</p>
                <p class="font-medium text-gray-800">{{ $petaniProfile->luas_lahan }} Ha</p>
            </div>
            @if ($petaniProfile->alamat)
                <div class="col-span-2">
                    <p class="text-gray-500">Alamat</p>
                    <p class="font-medium text-gray-800">{{ $petaniProfile->alamat }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Daftar Lahan --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-800">Daftar Lahan Saya</h2>
            <a href="{{ route('petani.lahan.create') }}"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg border border-blue-700">
                + Tambah Lahan
            </a>
        </div>

        @if ($lahans->isEmpty())
            <div class="text-center py-8 text-gray-400">
                <p class="text-4xl mb-3">🌱</p>
                <p class="text-sm">Belum ada lahan yang terdaftar.</p>
                <p class="text-xs mt-1">Klik "+ Tambah Lahan" untuk menambahkan lahan pertama Anda.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">Nama Lahan</th>
                            <th class="px-4 py-3">Luas (Ha)</th>
                            <th class="px-4 py-3">Jenis Tanah</th>
                            <th class="px-4 py-3">Tanaman Aktif</th>
                            <th class="px-4 py-3">Perkiraan Panen</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($lahans as $lahan)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $lahan->nama_lahan }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $lahan->luas_ha }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $lahan->jenis_tanah ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $lahan->tanaman_aktif ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $lahan->perkiraan_panen
                                        ? \Carbon\Carbon::parse($lahan->perkiraan_panen)->format('d M Y')
                                        : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $badge = match ($lahan->status ?? 'bera') {
                                            'aktif'  => 'bg-green-100 text-green-700',
                                            'panen'  => 'bg-blue-100 text-blue-700',
                                            default  => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badge }} capitalize">
                                        {{ $lahan->status ?? 'bera' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <a href="{{ route('petani.lahan.edit', $lahan) }}"
                                           class="px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-100 border border-blue-300 rounded-lg hover:bg-blue-200">
                                            ✏️ Edit
                                        </a>
                                        <form method="POST" action="{{ route('petani.lahan.destroy', $lahan) }}"
                                              onsubmit="return confirm('Hapus lahan ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-100 border border-red-300 rounded-lg hover:bg-red-200">
                                                🗑️ Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
