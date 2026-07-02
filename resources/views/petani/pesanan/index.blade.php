@extends('layouts.dashboard')
@section('title', 'Pesanan Masuk')
@section('content')

@if (session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-6">Pesanan Masuk</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Pembeli</th>
                    <th class="px-4 py-3">Jumlah</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Alamat</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($pesanans as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $p->produk->nama_produk }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $p->pembeli->username }}</td>
                        <td class="px-4 py-3">{{ $p->jumlah }} {{ $p->produk->satuan }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $p->alamat_pengiriman ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php $badge = ['pending'=>'yellow','diproses'=>'blue','selesai'=>'green','batal'=>'red'][$p->status_transaksi] ?? 'gray'; @endphp
                            <span class="px-2 py-1 text-xs font-semibold bg-{{ $badge }}-100 text-{{ $badge }}-700 rounded-full capitalize">{{ $p->status_transaksi }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($p->status_transaksi === 'pending')
                                <form method="POST" action="{{ route('petani.pesanan.konfirmasi', $p) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs bg-green-50 text-green-700 hover:bg-green-100 rounded-lg font-semibold">Konfirmasi</button>
                                </form>
                                <form method="POST" action="{{ route('petani.pesanan.tolak', $p) }}" class="inline ml-1" onsubmit="return confirm('Tolak pesanan ini?')">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs bg-red-50 text-red-700 hover:bg-red-100 rounded-lg font-semibold">Tolak</button>
                                </form>
                            @elseif ($p->status_transaksi === 'diproses')
                                <form method="POST" action="{{ route('petani.pesanan.selesai', $p) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg font-semibold">Selesai</button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada pesanan masuk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($pesanans->hasPages()) <div class="mt-4">{{ $pesanans->links() }}</div> @endif
</div>
@endsection
