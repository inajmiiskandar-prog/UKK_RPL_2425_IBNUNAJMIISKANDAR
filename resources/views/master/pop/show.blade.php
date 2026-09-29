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

<div class="grid gap-6 lg:grid-cols-3">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-5 flex items-center gap-3">
            <div class="rounded-full bg-blue-100 p-2 dark:bg-blue-900/30">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <h2 class="font-display text-lg font-bold text-gray-800 dark:text-white">{{ $pop->nama_pop }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $pop->kode_pop }} • {{ $pop->area->nama_area ?? '-' }}</p>
            </div>
        </div>

        <div class="space-y-4 text-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Alamat</p>
                <p class="mt-1 font-medium text-gray-800 dark:text-white">{{ $pop->alamat ?: '-' }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Latitude</p>
                    <p class="mt-1 break-all font-medium text-gray-800 dark:text-white">{{ $pop->latitude ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Longitude</p>
                    <p class="mt-1 break-all font-medium text-gray-800 dark:text-white">{{ $pop->longitude ?: '-' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 border-t border-gray-100 pt-4 dark:border-slate-700">
                <div class="rounded-xl bg-blue-50 p-3 dark:bg-blue-900/20">
                    <p class="text-xs text-blue-600 dark:text-blue-400">Jumlah OLT</p>
                    <p class="mt-1 text-xl font-bold text-blue-700 dark:text-blue-300">{{ $pop->olts->count() }}</p>
                </div>
                <div class="rounded-xl bg-purple-50 p-3 dark:bg-purple-900/20">
                    <p class="text-xs text-purple-600 dark:text-purple-400">Jumlah ONT</p>
                    <p class="mt-1 text-xl font-bold text-purple-700 dark:text-purple-300">{{ $pop->onts->count() }}</p>
                </div>
            </div>
        </div>

        @if($pop->olts->count())
            <div class="mt-6 border-t border-gray-100 pt-4 dark:border-slate-700">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">OLT Terhubung</h3>
                <div class="mt-3 space-y-2">
                    @foreach($pop->olts as $olt)
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-slate-700/50">
                            <span class="text-sm text-gray-700 dark:text-gray-200">{{ $olt->nama_olt }}</span>
                            <span class="text-xs text-gray-400">{{ $olt->kode_olt }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-3 flex flex-wrap gap-4 text-xs">
            <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-blue-600"></span> POP</span>
            <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-green-600"></span> OLT</span>
        </div>
        <div class="mb-3 relative">
            <input type="text" id="search-input" placeholder="Cari alamat/lokasi..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 pl-12 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder:text-gray-400">
            <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <div id="map" class="h-80 w-full rounded-xl border border-gray-200 dark:border-slate-600 sm:h-[26rem]"></div>
    </div>
</div>

@php
    $oltMapData = $pop->olts->map(function ($olt) {
        return [
            'nama' => $olt->nama_olt,
            'kode' => $olt->kode_olt,
            'latitude' => $olt->latitude,
            'longitude' => $olt->longitude,
        ];
    })->values()->all();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    var popLat = @json($pop->latitude);
    var popLng = @json($pop->longitude);
    var oltData = @json($oltMapData);
    var map = L.map('map');

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var popIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background:#8b5cf6;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 6px rgba(0,0,0,0.3);border:3px solid white;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" viewBox="0 0 20 20"><path d="M10 0C6.13 0 3 3.13 3 7c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg></div>',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40]
    });

    var oltIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background:#22c55e;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 6px rgba(0,0,0,0.3);border:3px solid white;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="white" viewBox="0 0 20 20"><path d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg></div>',
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });

    var markers = [];
    if (popLat && popLng) {
        L.marker([popLat, popLng], { icon: popIcon }).addTo(map)
            .bindPopup('<b>POP: {{ $pop->nama_pop }}</b><br>{{ $pop->kode_pop }}')
            .openPopup();
        markers.push([popLat, popLng]);
    }

    oltData.forEach(function(olt) {
        if (olt.latitude && olt.longitude) {
            L.marker([olt.latitude, olt.longitude], { icon: oltIcon }).addTo(map)
                .bindPopup('<b>OLT: ' + olt.nama + '</b><br>' + olt.kode);
            markers.push([olt.latitude, olt.longitude]);
        }
    });

    if (markers.length > 1) {
        map.fitBounds(markers, { padding: [40, 40] });
    } else if (markers.length) {
        map.setView(markers[0], 15);
    } else {
        map.setView([-2.5, 118], 5);
    }

    var searchMarker = null;
    document.getElementById('search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && this.value) {
            fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(this.value))
                .then(response => response.json())
                .then(data => {
                    if (!data.length) return alert('Lokasi tidak ditemukan');
                    var result = data[0];
                    if (searchMarker) map.removeLayer(searchMarker);
                    searchMarker = L.marker([result.lat, result.lon]).addTo(map).bindPopup(result.display_name).openPopup();
                    map.setView([result.lat, result.lon], 16);
                })
                .catch(() => alert('Gagal mencari lokasi'));
        }
    });
});
</script>
@endsection
