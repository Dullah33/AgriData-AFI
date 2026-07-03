@extends('layouts.dashboard')

@section('title', 'Manajemen Petugas Penyuluh')

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
        <h2 class="text-lg font-semibold text-gray-800">Manajemen Petugas Penyuluh</h2>
        <a href="{{ route('admin.penyuluh.create') }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
            + Tambah Penyuluh
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.penyuluh.index') }}" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari username, email, NIP, atau wilayah..."
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">

        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>

        <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-sm rounded-lg">Filter</button>
        <a href="{{ route('admin.penyuluh.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:underline self-center">Reset</a>
    </form>

    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Username / Email</th>
                    <th class="px-4 py-3">NIP</th>
                    <th class="px-4 py-3">Wilayah Binaan</th>
                    <th class="px-4 py-3">Telepon</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($penyuluhs as $penyuluh)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $penyuluh->user->username ?? '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $penyuluh->user->email ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $penyuluh->nip ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $penyuluh->wilayah_binaan }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $penyuluh->phone ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($penyuluh->status === 'aktif')
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Aktif</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-gray-200 text-gray-600 rounded-full">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.penyuluh.show', $penyuluh) }}"
                                   class="px-3 py-1 text-xs bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg font-semibold">
                                    Detail
                                </a>
                                <a href="{{ route('admin.penyuluh.edit', $penyuluh) }}"
                                   class="px-3 py-1 text-xs bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg font-semibold">
                                    Edit
                                </a>
                                @if ($penyuluh->status === 'aktif')
                                    <form method="POST" action="{{ route('admin.penyuluh.nonaktifkan', $penyuluh) }}"
                                          onsubmit="return confirm('Nonaktifkan akun penyuluh ini?')">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1 text-xs bg-yellow-50 text-yellow-700 hover:bg-yellow-100 rounded-lg font-semibold">
                                            Nonaktifkan
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.penyuluh.aktifkan', $penyuluh) }}">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1 text-xs bg-green-50 text-green-700 hover:bg-green-100 rounded-lg font-semibold">
                                            Aktifkan
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.penyuluh.destroy', $penyuluh) }}"
                                      onsubmit="return confirm('Hapus akun penyuluh ini beserta akun user-nya? Tindakan ini tidak bisa dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1 text-xs bg-red-50 text-red-700 hover:bg-red-100 rounded-lg font-semibold">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                            Belum ada akun Petugas Penyuluh yang terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($penyuluhs->hasPages())
        <div class="mt-4">{{ $penyuluhs->links() }}</div>
    @endif
</div>

@endsection
