@extends('layouts.dashboard')

@section('title', 'Detail ODP')
@section('page-title', 'Detail ODP')
@section('page-breadcrumb', 'Master Data / ODP / Detail')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('masterdata.odp.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
    <a href="{{ route('masterdata.odp.edit', $odp->id_odp) }}" class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-5 flex items-center gap-3">
            <div class="rounded-full bg-orange-100 p-2 dark:bg-orange-900/30">
                <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4"/></svg>
            </div>
            <div>
                <h2 class="font-display text-lg font-bold text-gray-800 dark:text-white">{{ $odp->nama_odp }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $odp->kode_odp }} • {{ $odp->olt->nama_olt ?? '-' }}</p>
            </div>
        </div>

        <div class="space-y-4 text-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Alamat</p>
                <p class="mt-1 font-medium text-gray-800 dark:text-white">{{ $odp->alamat ?: '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Jaringan</p>
                <p class="mt-1 font-medium text-gray-800 dark:text-white">{{ $odp->olt->nama_olt ?? '-' }} <span class="text-gray-400">•</span> {{ $odp->olt->pop->nama_pop ?? '-' }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Latitude</p>
                    <p class="mt-1 break-all font-medium text-gray-800 dark:text-white">{{ $odp->latitude ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Longitude</p>
                    <p class="mt-1 break-all font-medium text-gray-800 dark:text-white">{{ $odp->longitude ?: '-' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 border-t border-gray-100 pt-4 dark:border-slate-700">
                <div class="rounded-xl bg-orange-50 p-3 dark:bg-orange-900/20"><p class="text-xs text-orange-600 dark:text-orange-400">Total Port</p><p class="mt-1 text-xl font-bold text-orange-700 dark:text-orange-300">{{ $odp->jumlah_port ?? 0 }}</p></div>
                <div class="rounded-xl bg-blue-50 p-3 dark:bg-blue-900/20"><p class="text-xs text-blue-600 dark:text-blue-400">Tersedia</p><p class="mt-1 text-xl font-bold text-blue-700 dark:text-blue-300">{{ $odp->stok_port ?? 0 }}</p></div>
                <div class="rounded-xl bg-green-50 p-3 dark:bg-green-900/20"><p class="text-xs text-green-600 dark:text-green-400">Terpakai</p><p class="mt-1 text-xl font-bold text-green-700 dark:text-green-300">{{ ($odp->jumlah_port ?? 0) - ($odp->stok_port ?? 0) }}</p></div>
            </div>
            <div class="rounded-xl bg-purple-50 p-3 dark:bg-purple-900/20">
                <p class="text-xs text-purple-600 dark:text-purple-400">ONT Terhubung</p>
                <p class="mt-1 text-xl font-bold text-purple-700 dark:text-purple-300">{{ $odp->onts->count() }}</p>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-3 flex flex-wrap gap-4 text-xs">
            <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-orange-500"></span> ODP</span>
            <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-green-600"></span> OLT</span>
            <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-blue-600"></span> POP</span>
            <span class="flex items-center gap-1"><span class="h-1 w-4 bg-orange-500"></span> Rute</span>
        </div>
        <div class="mb-3 relative">
            <input type="text" id="search-input" placeholder="Cari alamat/lokasi..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 pl-12 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder:text-gray-400">
            <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <div id="map" class="h-80 w-full rounded-xl border border-gray-200 dark:border-slate-600 sm:h-[26rem]"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var odpLat = {{ $odp->latitude ?? 'null' }};
    var odpLng = {{ $odp->longitude ?? 'null' }};
    var oltLat = {{ $odp->olt->latitude ?? 'null' }};
    var oltLng = {{ $odp->olt->longitude ?? 'null' }};
    var popLat = {{ $odp->olt->pop->latitude ?? 'null' }};
    var popLng = {{ $odp->olt->pop->longitude ?? 'null' }};

    var map = L.map('map').setView([-6.2, 106.8], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var odpIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background:#f97316;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 6px rgba(0,0,0,0.3);border:3px solid white;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="white" viewBox="0 0 20 20"><path d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4"/></svg></div>',
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });

    var oltIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background:#22c55e;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 6px rgba(0,0,0,0.3);border:3px solid white;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="white" viewBox="0 0 20 20"><path d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg></div>',
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });

    var popIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background:#3b82f6;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 6px rgba(0,0,0,0.3);border:3px solid white;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="white" viewBox="0 0 20 20"><path d="M10 0C6.13 0 3 3.13 3 7c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg></div>',
        iconSize: [36, 36],
        iconAnchor: [18, 36]
    });

    var bounds = [];

    if (odpLat && odpLng) {
        L.marker([odpLat, odpLng], { icon: odpIcon }).addTo(map)
            .bindPopup('<b>ODP: {{ $odp->nama_odp }}</b>').openPopup();
        bounds.push([odpLat, odpLng]);
    }

    if (oltLat && oltLng) {
        L.marker([oltLat, oltLng], { icon: oltIcon }).addTo(map)
            .bindPopup('<b>OLT: {{ $odp->olt->nama_olt ?? "OLT" }}</b>');
        bounds.push([oltLat, oltLng]);
    }

    if (popLat && popLng) {
        L.marker([popLat, popLng], { icon: popIcon }).addTo(map)
            .bindPopup('<b>POP: {{ $odp->olt->pop->nama_pop ?? "POP" }}</b>');
        bounds.push([popLat, popLng]);
    }

    if (bounds.length >= 2) {
        map.fitBounds(bounds, { padding: [50, 50] });
    } else if (bounds.length === 1) {
        map.setView(bounds[0], 15);
    }
});
</script>
@endsection
