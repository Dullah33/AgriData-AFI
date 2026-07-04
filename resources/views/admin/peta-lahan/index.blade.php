@extends('layouts.dashboard')

@section('title', 'Peta Lahan & Wilayah')

@section('content')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-semibold text-gray-800">Peta Lahan &amp; Wilayah</h2>
        <div class="flex gap-2 text-xs">
            <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700">{{ $wilayahList->count() }} wilayah</span>
            <span class="px-2 py-1 rounded-full bg-green-100 text-green-700">{{ $poligonLahan->count() }} lahan dipetakan</span>
        </div>
    </div>
    <p class="text-xs text-gray-400 mb-4">
        Titik biru = pusat wilayah. Area hijau = poligon lahan yang sudah digambar petani
        lewat halaman "Profil & Lahan Saya". Klik untuk lihat detail.
    </p>

    <script type="application/json" id="peta-wilayah-data">{!! json_encode($markerWilayah, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
    <script type="application/json" id="peta-lahan-data">{!! json_encode($poligonLahan, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>

    <div id="peta-lahan-wilayah" class="w-full rounded-lg border border-gray-200" style="height: 480px;"></div>
</div>

{{-- Tabel ringkasan per wilayah, fallback non-JS --}}
<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-base font-semibold text-gray-800 mb-4">Ringkasan per Wilayah</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Wilayah</th>
                    <th class="px-4 py-3">Koordinat</th>
                    <th class="px-4 py-3">Jumlah Petani</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($wilayahList as $wilayah)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $wilayah->nama_wilayah }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            @if ($wilayah->latitude && $wilayah->longitude)
                                {{ $wilayah->latitude }}, {{ $wilayah->longitude }}
                            @else
                                <span class="text-gray-400">Belum diset</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $wilayah->petani_profiles_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Belum ada data wilayah.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('peta-lahan-wilayah').setView([-7.5539, 111.6565], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var bounds = [];

    // Marker wilayah
    var wilayahData = JSON.parse(document.getElementById('peta-wilayah-data').textContent);
    wilayahData.forEach(function (w) {
        var coord = [w.latitude, w.longitude];
        bounds.push(coord);
        L.marker(coord).addTo(map)
            .bindPopup('<strong>' + w.nama_wilayah + '</strong><br>' + w.jumlah_petani + ' petani terdaftar');
    });

    // Poligon lahan per petani
    var lahanData = JSON.parse(document.getElementById('peta-lahan-data').textContent);
    lahanData.forEach(function (l) {
        var latlngs = l.koordinat.map(function (p) { return [p[0], p[1]]; });
        var polygon = L.polygon(latlngs, { color: '#16A34A', fillOpacity: 0.3 }).addTo(map);
        polygon.bindPopup(
            '<strong>' + l.nama_lahan + '</strong><br>' +
            'Petani: ' + l.petani + '<br>' +
            'Luas: ' + l.luas_ha + ' ha<br>' +
            'Tanaman: ' + (l.tanaman_aktif || '-') + '<br>' +
            'Status: ' + l.status
        );
        latlngs.forEach(function (c) { bounds.push(c); });
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [40, 40] });
    }
</script>

@endsection
