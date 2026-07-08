@extends('layouts.dashboard')

@section('title', 'Peta Lahan, Wilayah & Penyakit')

@section('content')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

{{-- Peringatan dini --}}
@if ($wilayahRentan->isNotEmpty())
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
        <strong>⚠ Peringatan dini:</strong> wilayah berikut punya {{ $ambangRentan }}+ laporan penyakit dalam 30 hari terakhir —
        @foreach ($wilayahRentan as $w)
            <span class="font-semibold">{{ $w['wilayah'] }} ({{ $w['jumlah'] }} laporan)</span>{{ !$loop->last ? ', ' : '' }}
        @endforeach
    </div>
@endif

{{-- Filter --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Peta Lahan, Wilayah &amp; Penyakit</h2>
        <div class="flex gap-2 text-xs">
            <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700">{{ $wilayahList->count() }} wilayah</span>
            <span class="px-2 py-1 rounded-full bg-green-100 text-green-700">{{ $poligonLahan->count() }} lahan dipetakan</span>
            <span class="px-2 py-1 rounded-full bg-red-100 text-red-700">{{ $laporans->total() }} laporan penyakit ({{ $tahun }})</span>
        </div>
    </div>
    <form method="GET" action="{{ route('admin.peta-lahan.index') }}" class="flex flex-wrap gap-3">
        <select name="nama_penyakit" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Jenis Penyakit</option>
            @foreach ($daftarPenyakit as $p)
                <option value="{{ $p }}" {{ request('nama_penyakit') === $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
        <select name="tingkat_risiko" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Tingkat Risiko</option>
            @foreach (['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'] as $val => $label)
                <option value="{{ $val }}" {{ request('tingkat_risiko') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="wilayah_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Wilayah</option>
            @foreach ($wilayahList as $w)
                <option value="{{ $w->id }}" {{ (string) request('wilayah_id') === (string) $w->id ? 'selected' : '' }}>{{ $w->nama_wilayah }}</option>
            @endforeach
        </select>
        <select name="tahun" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            @foreach (range(now()->year, now()->year - 3) as $y)
                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">Filter</button>
        <a href="{{ route('admin.peta-lahan.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:underline self-center">Reset</a>
    </form>
</div>

{{-- Peta --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-2">
        <p class="text-xs text-gray-400">
            Titik biru = pusat wilayah &middot; Area = poligon lahan (warna mengikuti tingkat risiko penyakit tertinggi
            yang pernah dilaporkan di lahan itu) &middot; Titik merah/oranye/hijau = laporan penyakit tanpa lahan spesifik.
            Klik area/titik untuk detail.
        </p>
        <label class="flex items-center gap-2 text-xs text-gray-600 whitespace-nowrap ml-4">
            <input type="checkbox" id="toggle-heatmap" class="rounded">
            Tampilkan Heatmap Kepadatan Penyakit
        </label>
    </div>

    <script type="application/json" id="peta-wilayah-data">{!! json_encode($markerWilayah, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
    <script type="application/json" id="peta-lahan-data">{!! json_encode($poligonLahan, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
    <script type="application/json" id="titik-penyakit-data">{!! json_encode($titikPenyakitTanpaLahan, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
    <script type="application/json" id="heatmap-data">{!! json_encode($heatmapPoints, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>

    <div id="peta-lahan-wilayah" class="w-full rounded-lg border border-gray-200" style="height: 520px;"></div>

    <div class="flex gap-4 mt-3 text-xs text-gray-500">
        <span>🔵 Pusat Wilayah</span>
        <span>🔴 Lahan/Laporan Risiko Tinggi</span>
        <span>🟠 Risiko Sedang</span>
        <span>🟢 Risiko Rendah / Tanpa Laporan</span>
    </div>
</div>

{{-- Grafik Tren --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-base font-semibold text-gray-800 mb-3">Tren Laporan Penyakit Bulanan — {{ $tahun }}</h3>
    <canvas id="chart-tren" height="80"></canvas>
</div>

{{-- Daftar laporan --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-base font-semibold text-gray-800 mb-4">Daftar Laporan Penyakit</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Penyakit</th>
                    <th class="px-4 py-3">Pelapor</th>
                    <th class="px-4 py-3">Lahan</th>
                    <th class="px-4 py-3">Wilayah</th>
                    <th class="px-4 py-3">Risiko</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($laporans as $l)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-600">{{ $l->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $l->nama_penyakit }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $l->user->username ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $l->lahan->nama_lahan ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $l->wilayah->nama_wilayah ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $l->badgeRisiko() }}">{{ ucfirst($l->tingkat_risiko) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $l->badgeStatus() }}">{{ ucfirst($l->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada laporan penyakit.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($laporans->hasPages())
        <div class="mt-4">{{ $laporans->links() }}</div>
    @endif
</div>

{{-- Tabel ringkasan per wilayah --}}
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
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>

<script>
    var warna = { tinggi: '#DC2626', sedang: '#F59E0B', rendah: '#16A34A' };

    var map = L.map('peta-lahan-wilayah').setView([-7.5539, 111.6565], 12);
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

    // Poligon lahan, warna & popup mengikuti status penyakit terkini
    var lahanData = JSON.parse(document.getElementById('peta-lahan-data').textContent);
    lahanData.forEach(function (l) {
        var latlngs = l.koordinat.map(function (p) { return [p[0], p[1]]; });
        var warnaLahan = warna[l.risiko_tertinggi] || '#16A34A';

        var polygon = L.polygon(latlngs, { color: warnaLahan, fillOpacity: 0.35 }).addTo(map);

        var infoPenyakit = l.laporan_penyakit.length
            ? '<hr class="my-1">' + l.laporan_penyakit.map(function (d) {
                  return '⚠ ' + d.nama_penyakit + ' (' + d.tingkat_risiko + ') - ' + d.tanggal;
              }).join('<br>')
            : '<hr class="my-1"><span style="color:#16A34A">Tidak ada laporan penyakit</span>';

        polygon.bindPopup(
            '<strong>' + l.nama_lahan + '</strong><br>' +
            'Pemilik: ' + l.petani + '<br>' +
            'Luas: ' + l.luas_ha + ' ha &middot; Tanaman: ' + (l.tanaman_aktif || '-') + '<br>' +
            'Status Lahan: ' + l.status +
            infoPenyakit
        );
        latlngs.forEach(function (c) { bounds.push(c); });
    });

    // Titik laporan penyakit yang tidak terkait lahan manapun
    var titikData = JSON.parse(document.getElementById('titik-penyakit-data').textContent);
    titikData.forEach(function (t) {
        var coord = [t.latitude, t.longitude];
        bounds.push(coord);
        L.circleMarker(coord, {
            radius: 8,
            color: warna[t.tingkat_risiko] || '#9CA3AF',
            fillColor: warna[t.tingkat_risiko] || '#9CA3AF',
            fillOpacity: 0.8
        }).addTo(map).bindPopup('<strong>' + t.nama_penyakit + '</strong><br>Risiko: ' + t.tingkat_risiko + '<br><em>Tidak terkait lahan spesifik</em>');
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [40, 40] });
    }

    // Layer heatmap (toggle manual lewat checkbox, default tersembunyi
    // supaya peta tidak penuh sesak saat baru dibuka)
    var heatmapData = JSON.parse(document.getElementById('heatmap-data').textContent);
    var heatLayer = L.heatLayer(heatmapData, { radius: 35, blur: 25, maxZoom: 15 });

    document.getElementById('toggle-heatmap').addEventListener('change', function (e) {
        if (e.target.checked) {
            map.addLayer(heatLayer);
        } else {
            map.removeLayer(heatLayer);
        }
    });

    // Grafik tren bulanan
    var ctx = document.getElementById('chart-tren').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Jumlah Laporan',
                data: @json($trenChart),
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>

@endsection
