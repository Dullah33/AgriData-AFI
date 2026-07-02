@extends('layouts.dashboard')

@section('title', 'Moderasi Ulasan')

@section('content')

{{-- Flash message --}}
@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Moderasi Ulasan &amp; Rating</h2>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.ulasan.index') }}" class="flex gap-3 mb-6">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="dihapus" {{ request('status') === 'dihapus' ? 'selected' : '' }}>Disembunyikan</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-sm rounded-lg">Filter</button>
        <a href="{{ route('admin.ulasan.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:underline self-center">Reset</a>
    </form>

    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Pembeli</th>
                    <th class="px-4 py-3">Rating</th>
                    <th class="px-4 py-3">Komentar</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($ulasans as $ulasan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $ulasan->transaksi->produk->nama_produk ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $ulasan->transaksi->pembeli->username ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ str_repeat('★', $ulasan->rating) }}{{ str_repeat('☆', 5 - $ulasan->rating) }}</td>
                        <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $ulasan->komentar ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($ulasan->status === 'aktif')
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Aktif</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-full">Disembunyikan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($ulasan->status === 'aktif')
                                <form method="POST" action="{{ route('admin.ulasan.sembunyikan', $ulasan) }}"
                                      onsubmit="return confirm('Sembunyikan ulasan ini?')">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-1 text-xs bg-red-50 text-red-700 hover:bg-red-100 rounded-lg font-semibold">
                                        Sembunyikan
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.ulasan.aktifkan', $ulasan) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-1 text-xs bg-green-50 text-green-700 hover:bg-green-100 rounded-lg font-semibold">
                                        Aktifkan
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                            Belum ada ulasan yang masuk.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($ulasans->hasPages())
        <div class="mt-4">{{ $ulasans->links() }}</div>
    @endif
</div>

@endsection
