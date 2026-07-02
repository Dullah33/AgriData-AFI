@extends('layouts.dashboard')

@section('title', 'Profil & Lahan Saya')

@section('content')

{{-- Flash message --}}
@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

{{-- Ringkasan profil --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Profil Petani</h2>
        @if ($petaniProfile->isTerverifikasi())
            <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Terverifikasi</span>
        @else
            <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">Menunggu Verifikasi Admin</span>
        @endif
    </div>
    <dl class="grid grid-cols-2 gap-y-3 text-sm">
        <dt class="text-gray-500">Kelompok Tani</dt>
        <dd class="text-gray-800">{{ $petaniProfile->nama_kelompok_tani ?? '-' }}</dd>

        <dt class="text-gray-500">Wilayah</dt>
        <dd class="text-gray-800">{{ $petaniProfile->wilayah->nama_wilayah ?? '-' }}</dd>

        <dt class="text-gray-500">Komoditas Utama</dt>
        <dd class="text-gray-800">{{ $petaniProfile->komoditas_utama ?? '-' }}</dd>

        <dt class="text-gray-500">Total Luas Lahan (Terdaftar)</dt>
        <dd class="text-gray-800">{{ $lahans->sum('luas_ha') }} ha dari {{ $lahans->count() }} bidang lahan</dd>
    </dl>
</div>

{{-- Daftar lahan --}}
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Daftar Lahan Saya</h2>
        <a href="{{ route('petani.lahan.create') }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
            + Tambah Lahan
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Nama Lahan</th>
                    <th class="px-4 py-3">Luas</th>
                    <th class="px-4 py-3">Jenis Tanah</th>
                    <th class="px-4 py-3">Tanaman Aktif</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($lahans as $lahan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $lahan->nama_lahan }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $lahan->luas_ha }} ha</td>
                        <td class="px-4 py-3 text-gray-600">{{ $lahan->jenis_tanah ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $lahan->tanaman_aktif ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badge = match ($lahan->status) {
                                    'aktif' => 'bg-green-100 text-green-700',
                                    'panen' => 'bg-blue-100 text-blue-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badge }}">
                                {{ ucfirst($lahan->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('petani.lahan.edit', $lahan) }}"
                               class="px-3 py-1 text-xs bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg font-semibold">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('petani.lahan.destroy', $lahan) }}"
                                  onsubmit="return confirm('Hapus data lahan ini?')">
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
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                            Belum ada lahan terdaftar. Klik "Tambah Lahan" untuk menambahkan yang pertama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
