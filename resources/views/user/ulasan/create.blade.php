@extends('layouts.dashboard')
@section('title', 'Beri Ulasan')
@section('content')

<div class="max-w-xl">
    <a href="{{ route('user.pesanan') }}" class="text-sm text-blue-600 hover:underline block mb-4">← Kembali ke Pesanan Saya</a>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-1">Beri Ulasan & Rating</h2>

        {{-- Info Transaksi --}}
        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm space-y-1">
            <p><span class="text-gray-500">Produk:</span> <strong>{{ $transaksi->produk->nama_produk }}</strong></p>
            <p><span class="text-gray-500">Petani:</span> {{ $transaksi->produk->petani->username }}</p>
            <p><span class="text-gray-500">Total:</span> Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('user.ulasan.store', $transaksi) }}" class="space-y-5">
            @csrf

            {{-- Star Rating --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Rating <span class="text-red-500">*</span></label>
                <div class="flex gap-2" id="star-container">
                    @for ($i = 1; $i <= 5; $i++)
                        <label class="cursor-pointer">
                            <input type="radio" name="rating" value="{{ $i }}"
                                   class="sr-only" {{ old('rating') == $i ? 'checked' : '' }}
                                   onchange="updateStars({{ $i }})">
                            <span id="star-{{ $i }}"
                                  class="text-4xl transition-colors {{ old('rating') >= $i ? 'text-amber-400' : 'text-gray-300' }} hover:text-amber-400"
                                  onclick="updateStars({{ $i }})">★</span>
                        </label>
                    @endfor
                </div>
                <p class="text-xs text-gray-400 mt-1" id="rating-label">Pilih rating...</p>
            </div>

            {{-- Komentar --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Komentar (opsional)</label>
                <textarea name="komentar" rows="4"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Ceritakan pengalaman berbelanja Anda...">{{ old('komentar') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Maksimal 500 karakter</p>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg border border-amber-600">
                    ⭐ Kirim Ulasan
                </button>
                <a href="{{ route('user.pesanan') }}"
                   class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const labels = ['', 'Sangat Buruk', 'Buruk', 'Cukup', 'Bagus', 'Sangat Bagus'];
function updateStars(val) {
    for (let i = 1; i <= 5; i++) {
        document.getElementById('star-' + i).className =
            'text-4xl transition-colors ' + (i <= val ? 'text-amber-400' : 'text-gray-300') + ' hover:text-amber-400';
    }
    document.getElementById('rating-label').textContent = labels[val] + ' (' + val + '/5)';
    document.querySelector('input[name="rating"][value="' + val + '"]').checked = true;
}
</script>
@endsection
