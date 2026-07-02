@extends('layouts.dashboard')

@section('content')
<!-- Memanggil CSS Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>

<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Tambah Data Lahan</h2>

        <form action="{{ route('petani.lahan.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="land_name" class="block text-gray-700 font-semibold mb-2">Nama Lahan</label>
                    <input type="text" name="land_name" id="land_name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Misal: Sawah Blok A" required>
                </div>
                <div>
                    <label for="area_ha" class="block text-gray-700 font-semibold mb-2">Luas Lahan (Hektar)</label>
                    <input type="number" step="0.01" name="area_ha" id="area_ha" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Misal: 1.5" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="soil_type" class="block text-gray-700 font-semibold mb-2">Jenis Tanah</label>
                    <input type="text" name="soil_type" id="soil_type" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Misal: Litosol, Aluvial" required>
                </div>
                <div>
                    <label for="status" class="block text-gray-700 font-semibold mb-2">Status Lahan</label>
                    <select name="status" id="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" required>
                        <option value="aktif">Aktif</option>
                        <option value="bera">Bera (Istirahat)</option>
                        <option value="panen">Masa Panen</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="current_crop" class="block text-gray-700 font-semibold mb-2">Tanaman Aktif Saat Ini</label>
                <input type="text" name="current_crop" id="current_crop" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Misal: Padi, Jagung (Opsional)">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Gambarkan Area Lahan (Peta)</label>
                <p class="text-sm text-gray-500 mb-2">Gunakan tool kotak/polygon di pojok kiri peta untuk menggambar batas area lahan kamu.</p>
                
                <!-- ID div map untuk merender peta Leaflet -->
                <div id="map" class="h-96 w-full border border-gray-300 rounded-lg z-0"></div>
                
                <!-- Input hidden yang isinya otomatis keisi data JSON poligon -->
                <input type="hidden" name="polygon_coordinates" id="polygon_coordinates" required>
            </div>

            <div class="flex justify-end mt-6">
                <a href="{{ route('petani.lahan.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-2">Batal</a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg">Simpan Lahan</button>
            </div>
        </form>
    </div>
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

        // Ubah bentuk yang digambar ke format JSON (GeoJSON) lalu ditampung ke form hidden
        var geojson = layer.toGeoJSON();
        document.getElementById('polygon_coordinates').value = JSON.stringify(geojson.geometry.coordinates);
    });
</script>
@endsection