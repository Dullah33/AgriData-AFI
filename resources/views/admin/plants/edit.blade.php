@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Edit Tanaman: {{ $plant->nama }}</h1>
            <p class="text-gray-600 mt-1">Update data tanaman</p>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.plants.update', $plant->id) }}" method="POST" class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
            @csrf
            @method('PUT')

            <!-- Basic Info -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Dasar</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kode Tanaman *</label>
                        <input type="text" name="kode" value="{{ old('kode', $plant->kode) }}" required
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                        @error('kode') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Tanaman *</label>
                        <input type="text" name="nama" value="{{ old('nama', $plant->nama) }}" required
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                        @error('nama') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">URL Gambar</label>
                        <input type="text" name="gambar" value="{{ old('gambar', $plant->gambar) }}" 
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" 
                                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">{{ old('deskripsi', $plant->deskripsi) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Keunggulan</label>
                        <textarea name="keunggulan" rows="3" 
                                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">{{ old('keunggulan', $plant->keunggulan) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Climate Requirements -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Kebutuhan Iklim</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Suhu Ideal (Teks) *</label>
                        <input type="text" name="suhu_ideal" value="{{ old('suhu_ideal', $plant->suhu_ideal) }}" required
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Min Suhu (°C) *</label>
                            <input type="number" step="0.01" name="min_suhu" value="{{ old('min_suhu', $plant->min_suhu) }}" required
                                   class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Max Suhu (°C) *</label>
                            <input type="number" step="0.01" name="max_suhu" value="{{ old('max_suhu', $plant->max_suhu) }}" required
                                   class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kelembapan Ideal (Teks) *</label>
                        <input type="text" name="kelembapan_ideal" value="{{ old('kelembapan_ideal', $plant->kelembapan_ideal) }}" required
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Min Kelembaban (%) *</label>
                            <input type="number" step="0.01" name="min_kelembaban" value="{{ old('min_kelembaban', $plant->min_kelembaban) }}" required
                                   class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Max Kelembaban (%) *</label>
                            <input type="number" step="0.01" name="max_kelembaban" value="{{ old('max_kelembaban', $plant->max_kelembaban) }}" required
                                   class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Curah Hujan Ideal *</label>
                        <input type="text" name="curah_hujan_ideal" value="{{ old('curah_hujan_ideal', $plant->curah_hujan_ideal) }}" required
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Musim Tanam *</label>
                        <input type="text" name="musim" value="{{ old('musim', $plant->musim) }}" required
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Tanah *</label>
                        <input type="text" name="jenis_tanah" value="{{ old('jenis_tanah', $plant->jenis_tanah) }}" required
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Durasi Panen</label>
                        <input type="text" name="durasi_panen" value="{{ old('durasi_panen', $plant->durasi_panen) }}"
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Weather Status -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Status Cuaca</h2>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status Cuaca Hujan</label>
                        <textarea name="status_cuaca_hujan" rows="2" 
                                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">{{ old('status_cuaca_hujan', $plant->status_cuaca_hujan) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status Cuaca Panas</label>
                        <textarea name="status_cuaca_panas" rows="2" 
                                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">{{ old('status_cuaca_panas', $plant->status_cuaca_panas) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status Curah Hujan Tinggi</label>
                        <textarea name="status_curah_hujan_tinggi" rows="2" 
                                class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">{{ old('status_curah_hujan_tinggi', $plant->status_curah_hujan_tinggi) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status Curah Hujan Rendah</label>
                        <textarea name="status_curah_hujan_rendah" rows="2" 
                                class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">{{ old('status_curah_hujan_rendah', $plant->status_curah_hujan_rendah) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Budidaya Info -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Budidaya</h2>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Persiapan Lahan</label>
                        <textarea name="budidaya_persiapan_lahan" rows="3" 
                                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">{{ old('budidaya_persiapan_lahan', $plant->budidaya_persiapan_lahan) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Pemupukan</label>
                        <textarea name="budidaya_pemupukan" rows="3" 
                                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">{{ old('budidaya_pemupukan', $plant->budidaya_pemupukan) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Irigasi</label>
                        <textarea name="budidaya_irigasi" rows="3" 
                                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">{{ old('budidaya_irigasi', $plant->budidaya_irigasi) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Langkah Budidaya (Dynamic) -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Langkah Budidaya</h2>
                <div id="steps-container">
                    @php
                        $steps = is_string($plant->langkah_budidaya) ? json_decode($plant->langkah_budidaya, true) : ($plant->langkah_budidaya ?? []);
                    @endphp
                    @forelse($steps as $index => $step)
                    <div class="step-item mb-4 p-4 border-2 border-gray-200 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Langkah</label>
                                <input type="text" name="langkah_budidaya[{{ $index }}][title]" value="{{ $step['title'] ?? '' }}"
                                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Konten</label>
                                <textarea name="langkah_budidaya[{{ $index }}][content]" rows="2" 
                                          class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">{{ $step['content'] ?? '' }}</textarea>
                            </div>
                        </div>
                        @if($index > 0)
                        <button type="button" onclick="this.parentElement.remove()" 
                                class="mt-2 px-3 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-sm">
                            Hapus
                        </button>
                        @endif
                    </div>
                    @empty
                    <div class="step-item mb-4 p-4 border-2 border-gray-200 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Langkah</label>
                                <input type="text" name="langkah_budidaya[0][title]" 
                                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Konten</label>
                                <textarea name="langkah_budidaya[0][content]" rows="2" 
                                          class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"></textarea>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>
                <button type="button" onclick="addStep()" 
                        class="mt-2 px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition font-semibold">
                    + Tambah Langkah
                </button>
            </div>

            <!-- Tips Budidaya (Dynamic) -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Tips Budidaya</h2>
                <div id="tips-container">
                    @php
                        $tips = is_string($plant->tips_budidaya) ? json_decode($plant->tips_budidaya, true) : ($plant->tips_budidaya ?? []);
                    @endphp
                    @forelse($tips as $index => $tip)
                    <div class="tip-item mb-4 p-4 border-2 border-gray-200 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Icon (Emoji)</label>
                                <input type="text" name="tips_budidaya[{{ $index }}][icon]" value="{{ $tip['icon'] ?? '' }}"
                                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Tips</label>
                                <input type="text" name="tips_budidaya[{{ $index }}][title]" value="{{ $tip['title'] ?? '' }}"
                                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Konten</label>
                                <textarea name="tips_budidaya[{{ $index }}][content]" rows="2" 
                                          class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">{{ $tip['content'] ?? '' }}</textarea>
                            </div>
                        </div>
                        @if($index > 0)
                        <button type="button" onclick="this.parentElement.remove()" 
                                class="mt-2 px-3 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-sm">
                            Hapus
                        </button>
                        @endif
                    </div>
                    @empty
                    <div class="tip-item mb-4 p-4 border-2 border-gray-200 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Icon (Emoji)</label>
                                <input type="text" name="tips_budidaya[0][icon]" placeholder="💡" 
                                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Tips</label>
                                <input type="text" name="tips_budidaya[0][title]" 
                                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Konten</label>
                                <textarea name="tips_budidaya[0][content]" rows="2" 
                                          class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"></textarea>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>
                <button type="button" onclick="addTip()" 
                        class="mt-2 px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition font-semibold">
                    + Tambah Tips
                </button>
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-4">
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all">
                    Update Tanaman
                </button>
                <a href="{{ route('admin.plants.index') }}" 
                   class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl transition-all">
                    Batal
                </a>
            </div>
        </form>

    </div>
</div>

<script>
let stepCount = {{ count($steps ?? []) }};
let tipCount = {{ count($tips ?? []) }};

function addStep() {
    const container = document.getElementById('steps-container');
    const newStep = document.createElement('div');
    newStep.className = 'step-item mb-4 p-4 border-2 border-gray-200 rounded-lg';
    newStep.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Langkah</label>
                <input type="text" name="langkah_budidaya[${stepCount}][title]" 
                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Konten</label>
                <textarea name="langkah_budidaya[${stepCount}][content]" rows="2" 
                          class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"></textarea>
            </div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" 
                class="mt-2 px-3 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-sm">
            Hapus
        </button>
    `;
    container.appendChild(newStep);
    stepCount++;
}

function addTip() {
    const container = document.getElementById('tips-container');
    const newTip = document.createElement('div');
    newTip.className = 'tip-item mb-4 p-4 border-2 border-gray-200 rounded-lg';
    newTip.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Icon (Emoji)</label>
                <input type="text" name="tips_budidaya[${tipCount}][icon]" placeholder="💡" 
                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Tips</label>
                <input type="text" name="tips_budidaya[${tipCount}][title]" 
                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Konten</label>
                <textarea name="tips_budidaya[${tipCount}][content]" rows="2" 
                          class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"></textarea>
            </div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" 
                class="mt-2 px-3 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-sm">
            Hapus
        </button>
    `;
    container.appendChild(newTip);
    tipCount++;
}
</script>
@endsection