@extends('layouts.dashboard')

@section('title', 'Edit Petugas Penyuluh')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.penyuluh.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">&larr; Kembali</a>
        <h2 class="text-lg font-semibold text-gray-800">Edit Penyuluh: {{ $penyuluh->user->username ?? '-' }}</h2>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.penyuluh.update', $penyuluh) }}" class="space-y-5">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text" value="{{ $penyuluh->user->username ?? '-' }}" disabled
                   class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-500">
            <p class="text-xs text-gray-400 mt-1">Username &amp; email tidak bisa diubah dari sini.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
            <input type="text" name="nip" value="{{ old('nip', $penyuluh->nip) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Nomor Induk Pegawai (opsional)">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Wilayah Binaan <span class="text-red-500">*</span></label>
            <input type="text" name="wilayah_binaan" value="{{ old('wilayah_binaan', $penyuluh->wilayah_binaan) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Nama desa/kecamatan yang dibina">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
            <input type="text" name="phone" value="{{ old('phone', $penyuluh->phone) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="08xxxxxxxxxx (opsional)">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
            <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="aktif" {{ old('status', $penyuluh->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ old('status', $penyuluh->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.penyuluh.index') }}"
               class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
