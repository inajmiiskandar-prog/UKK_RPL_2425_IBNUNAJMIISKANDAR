@extends('layouts.dashboard')

@section('title', 'Detail POP')
@section('page-title', 'Detail POP')
@section('page-breadcrumb', 'Master Data / POP / Detail')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('masterdata.pop.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
    <a href="{{ route('masterdata.pop.edit', $pop->id_pop) }}" class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="mb-4 flex items-center gap-3">
        <div class="rounded-full bg-blue-100 p-2 dark:bg-blue-900/30">
            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-bold text-gray-800 dark:text-white">{{ $pop->nama_pop }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $pop->kode_pop }} • {{ $pop->area->nama_area ?? '-' }}</p>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="mb-3">
        <div class="relative">
            <input type="text" id="search-input" placeholder="Cari alamat/lokasi..."
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 pl-12 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder:text-gray-400">
            <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>

    {{-- Peta Lokasi POP --}}
    <div id="map" class="h-[500px] w-full rounded-xl border border-gray-200 dark:border-slate-600"></div>

    @if($pop->alamat)
    <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
        <span class="font-medium">Alamat:</span> {{ $pop->alamat }}
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var lat = {{ $pop->latitude ?? '-6.9' }};
    var lng = {{ $pop->longitude ?? '107.6' }};
    var namaPop = "{{ $pop->nama_pop }}";
    var kodePop = "{{ $pop->kode_pop }}";

    var map = L.map('map').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Custom icon
    var customIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background:#8b5cf6;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 6px rgba(0,0,0,0.3);border:3px solid white;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" viewBox="0 0 20 20"><path d="M10 0C6.13 0 3 3.13 3 7c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg></div>',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40]
    });

    var marker = L.marker([lat, lng], { icon: customIcon, draggable: true }).addTo(map);

    marker.bindPopup('<b>' + namaPop + '</b><br>' + kodePop).openPopup();

    // Search function
    var searchMarker = null;
    document.getElementById('search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            var query = this.value;
            if (query) {
                fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            var result = data[0];
                            var newLat = parseFloat(result.lat);
                            var newLng = parseFloat(result.lon);

                            if (searchMarker) {
                                map.removeLayer(searchMarker);
                            }

                            searchMarker = L.marker([newLat, newLng], {
                                icon: L.divIcon({
                                    className: 'search-marker',
                                    html: '<div style="background:#ef4444;border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 4px rgba(0,0,0,0.3);border:3px solid white;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="white" viewBox="0 0 20 20"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>',
                                    iconSize: [30, 30],
                                    iconAnchor: [15, 15]
                                })
                            }).addTo(map);

                            searchMarker.bindPopup('<b>Hasil Pencarian</b><br>' + result.display_name).openPopup();
                            map.setView([newLat, newLng], 16);
                        } else {
                            alert('Lokasi tidak ditemukan');
                        }
                    })
                    .catch(err => alert('Gagal mencari lokasi'));
            }
        }
    });

    // Update marker position on drag
    marker.on('dragend', function(e) {
        var pos = marker.getLatLng();
        marker.setPopupContent('<b>' + namaPop + '</b><br>' + kodePop + '<br>Lat: ' + pos.lat.toFixed(6) + '<br>Lng: ' + pos.lng.toFixed(6)).openPopup();
    });
});
</script>
@endsection
