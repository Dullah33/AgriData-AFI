@extends('layouts.dashboard')
@section('title', 'Dashboard User')
@section('content')

<div class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">Selamat datang, {{ Auth::user()->username }} 👋</h2>
        <p class="text-sm text-gray-500">Ringkasan aktivitas belanja Anda.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500 uppercase font-semibold">Pesanan Aktif</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['pesanan_aktif'] }}</p>
            <p class="text-xs text-gray-400 mt-1">pending & diproses</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-xs text-gray-500 uppercase font-semibold">Pesanan Selesai</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['pesanan_selesai'] }}</p>
            <p class="text-xs text-gray-400 mt-1">total transaksi</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-amber-500">
            <p class="text-xs text-gray-500 uppercase font-semibold">Belum Diulas</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['belum_diulas'] }}</p>
            <p class="text-xs text-gray-400 mt-1">menunggu ulasan</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 text-sm">Pesanan Terbaru</h3>
                <a href="{{ route('user.pesanan') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
            </div>
            @forelse ($pesananTerbaru as $p)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0 text-sm">
                    <div>
                        <p class="font-medium text-gray-800">{{ $p->produk->nama_produk }}</p>
                        <p class="text-xs text-gray-400">{{ $p->created_at->format('d M Y') }}</p>
                    </div>
                    @php $badge = ['pending'=>'yellow','diproses'=>'blue','selesai'=>'green','batal'=>'red'][$p->status_transaksi] ?? 'gray'; @endphp
                    <span class="px-2 py-1 text-xs font-semibold bg-{{ $badge }}-100 text-{{ $badge }}-700 rounded-full capitalize">{{ $p->status_transaksi }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-2">Belum ada pesanan.</p>
            @endforelse
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 text-sm">Produk Terbaru di Marketplace</h3>
                <a href="{{ route('user.marketplace') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
            </div>
            @forelse ($produkTerbaru as $p)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0 text-sm">
                    <div>
                        <p class="font-medium text-gray-800">{{ $p->nama_produk }}</p>
                        <p class="text-xs text-gray-400">{{ $p->petani->username }}</p>
                    </div>
                    <span class="text-green-700 font-semibold text-xs">Rp {{ number_format($p->harga, 0, ',', '.') }}/{{ $p->satuan }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-2">Belum ada produk tersedia.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
