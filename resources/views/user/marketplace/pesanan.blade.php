@extends('layouts.dashboard')
@section('title', 'Riwayat Pesanan Saya')
@section('content')

@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-6">Riwayat Pesanan Saya</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Petani</th>
                    <th class="px-4 py-3">Jumlah</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Ulasan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($pesanans as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $p->produk->nama_produk }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $p->produk->petani->username }}</td>
                        <td class="px-4 py-3">{{ $p->jumlah }} {{ $p->produk->satuan }}</td>
                        <td class="px-4 py-3 font-semibold">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            @php $badge = ['pending'=>'yellow','diproses'=>'blue','selesai'=>'green','batal'=>'red'][$p->status_transaksi] ?? 'gray'; @endphp
                            <span class="px-2 py-1 text-xs font-semibold bg-{{ $badge }}-100 text-{{ $badge }}-700 rounded-full capitalize">
                                {{ $p->status_transaksi }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $p->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            @if ($p->status_transaksi === 'selesai')
                                @if ($p->ulasan)
                                    <div class="flex items-center gap-1">
                                        <span class="text-amber-400 font-bold">{{ $p->ulasan->rating }}★</span>
                                        <span class="text-xs text-gray-400">Sudah diulas</span>
                                    </div>
                                @else
                                    <a href="{{ route('user.ulasan.create', $p) }}"
                                       class="px-3 py-1.5 text-xs font-semibold text-amber-700 bg-amber-100 border border-amber-300 rounded-lg hover:bg-amber-200">
                                        ⭐ Beri Ulasan
                                    </a>
                                @endif
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada pesanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($pesanans->hasPages()) <div class="mt-4">{{ $pesanans->links() }}</div> @endif
</div>
@endsection