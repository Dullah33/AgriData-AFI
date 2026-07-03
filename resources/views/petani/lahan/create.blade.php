@extends('layouts.dashboard')

@section('title', 'Tambah Lahan')

@section('content')
<!-- Memanggil CSS Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>

<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('petani.lahan.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali</a>
        <h2 class="text-lg font-semibold text-gray-800">Tambah Lahan Baru</h2>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('petani.lahan.store') }}" class="space-y-4">
        @csrf

        {{-- Nama Lahan & Luas --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama/Kode Lahan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_lahan" value="{{ old('nama_lahan') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Misal: Sawah Blok A" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Luas Lahan (Ha) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" min="0.01" name="luas_ha" value="{{ old('luas_ha') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Misal: 1.5" required>
            </div>
        </div>

        {{-- Jenis Tanah --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Tanah</label>
            <select name="jenis_tanah"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">-- Pilih Jenis Tanah --</option>
                @foreach ($jenisTanahList as $j)
                    <option value="{{ $j }}" {{ old('jenis_tanah') === $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>
        </div>

        {{-- Tanaman Aktif --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanaman yang Sedang Ditanam</label>
            <input type="text" name="tanaman_aktif" value="{{ old('tanaman_aktif') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                   placeholder="Misal: Padi, Jagung — kosongkan jika lahan sedang bera">
        </div>

        {{-- Tanggal Tanam & Perkiraan Panen --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Tanam</label>
                <input type="date" name="tanggal_tanam" value="{{ old('tanggal_tanam') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Perkiraan Tanggal Panen</label>
                <input type="date" name="perkiraan_panen" value="{{ old('perkiraan_panen') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
        </div>

        {{-- Peta poligon lahan --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gambarkan Area Lahan (Peta)</label>
            <p class="text-xs text-gray-400 mb-2">
                Gunakan tool poligon di pojok kiri atas peta untuk menggambar batas area lahan kamu (opsional).
            </p>

            <!-- ID div map untuk merender peta Leaflet -->
            <div id="map" class="h-96 w-full border border-gray-300 rounded-lg z-0"></div>

            <!-- Input hidden berisi JSON poligon, dibaca controller sebagai koordinat_poligon -->
            <input type="hidden" name="koordinat_poligon" id="koordinat_poligon">
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
                💾 Simpan Lahan
            </button>
            <a href="{{ route('petani.lahan.index') }}"
               class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300">
                Batal
            </a>
        </div>
    </form>
</div>

<!-- Memanggil Javascript Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

<script>
    // Titik awal peta difokuskan langsung ke area Mejayan biar nggak perlu nyari-nyari lagi
    var map = L.map('map').setView([-7.5539, 111.6565], 13);

    // Render tampilan petanya
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Setup fitur untuk corat-coret/gambar lahan
    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    var drawControl = new L.Control.Draw({
        edit: {
            featureGroup: drawnItems
        },
        draw: {
            polygon: true,
            polyline: false,
            rectangle: false,
            circle: false,
            marker: false,
            circlemarker: false
        }
    });
    map.addControl(drawControl);

    // Script ini jalan setiap kali kamu selesai menggambar poligon di atas peta
    map.on(L.Draw.Event.CREATED, function (event) {
        var layer = event.layer;

        // Hapus area gambar yang lama kalau ada (hanya nyimpan 1 poligon tiap lahan)
        drawnItems.clearLayers();
        drawnItems.addLayer(layer);

        // Ubah bentuk yang digambar ke format JSON (pasangan [lat, lng]) lalu ditampung ke form hidden.
        // Dipakai apa adanya (bukan format GeoJSON [lng, lat]) supaya cocok dengan decodePoligon()
        // di LahanController yang mengecek minimal 3 titik koordinat.
        var latlngs = layer.getLatLngs()[0].map(function (p) { return [p.lat, p.lng]; });
        document.getElementById('koordinat_poligon').value = JSON.stringify(latlngs);
    });
</script>
@endsection
