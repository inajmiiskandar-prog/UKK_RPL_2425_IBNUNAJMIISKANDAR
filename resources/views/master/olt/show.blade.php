@extends('layouts.dashboard')

@section('title', 'Detail OLT')
@section('page-title', 'Detail OLT')
@section('page-breadcrumb', 'Master Data / OLT / Detail')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<style>
    .leaflet-routing-container {
        display: none !important;
    }
</style>

<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('masterdata.olt.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
    <a href="{{ route('masterdata.olt.edit', $olt->id_olt) }}" class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    {{-- Info Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-4 flex items-center gap-3">
            <div class="rounded-full bg-green-100 p-2 dark:bg-green-900/30">
                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
            </div>
            <div>
                <h2 class="font-display text-lg font-bold text-gray-800 dark:text-white">{{ $olt->nama_olt }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $olt->kode_olt }} • {{ $olt->pop->nama_pop ?? '-' }} • {{ $olt->pop->area->nama_area ?? '-' }}</p>
            </div>
        </div>

        {{-- Foto OLT --}}
        @if($olt->foto_olt)
        <div class="mb-4">
            <label class="mb-2 block text-xs font-medium text-gray-500">Foto OLT</label>
            <div class="relative overflow-hidden rounded-xl border border-gray-200 dark:border-slate-600">
                <img src="{{ asset('storage/' . $olt->foto_olt) }}" alt="Foto {{ $olt->nama_olt }}" class="h-48 w-full cursor-pointer object-cover transition hover:scale-[1.02]" onclick="openFullscreen(this)">
                <button type="button" onclick="openFullscreen(this.dataset.src)" data-src="{{ asset('storage/' . $olt->foto_olt) }}" class="absolute right-2 top-2 rounded-lg bg-black/50 p-2 text-white hover:bg-black/70" aria-label="Lihat foto OLT">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
            </div>
        </div>
        @else
        <div class="mb-4">
            <label class="mb-2 block text-xs font-medium text-gray-500">Foto OLT</label>
            <div class="flex h-48 w-full items-center justify-center rounded-xl border border-gray-200 bg-gray-50 dark:border-slate-600 dark:bg-slate-700">
                <div class="text-center text-gray-400">
                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="mt-2 text-sm">Belum ada foto</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Info Detail --}}
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">IP Address</span>
                <span class="font-medium dark:text-white">{{ $olt->ip_olt ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Username</span>
                <span class="font-medium dark:text-white">{{ $olt->username_olt ?? '-' }}</span>
            </div>
            @if($olt->lokasi)
            <div class="flex justify-between">
                <span class="text-gray-500">Lokasi</span>
                <span class="max-w-[60%] text-right font-medium dark:text-white">{{ $olt->lokasi }}</span>
            </div>
            @endif
            <div class="flex justify-between">
                <span class="text-gray-500">Latitude</span>
                <span class="font-medium dark:text-white">{{ $olt->latitude ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Longitude</span>
                <span class="font-medium dark:text-white">{{ $olt->longitude ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Jumlah ODP</span>
                <span class="font-medium dark:text-white">{{ $olt->odps->count() ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Jumlah Port</span>
                <span class="font-medium dark:text-white">{{ $olt->portPons->count() ?? 0 }}</span>
            </div>
        </div>
    </div>

    {{-- Map Card --}}
    <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        {{-- Legend --}}
        <div class="mb-3 flex flex-wrap gap-4 text-xs">
            <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-green-600"></span> OLT</span>
            <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-blue-600"></span> POP</span>
            <span class="flex items-center gap-1"><span class="h-3 w-3 bg-orange-500"></span> Rute</span>
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

        {{-- Peta --}}
        <div id="map" class="h-80 w-full rounded-xl border border-gray-200 dark:border-slate-600"></div>
    </div>
</div>

{{-- Fullscreen Modal --}}
<div id="fullscreenModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/90 p-4" onclick="closeFullscreen()">
    <button type="button" class="absolute right-4 top-4 rounded-lg bg-white/20 p-2 text-white hover:bg-white/30" aria-label="Tutup foto">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <img id="fullscreenImage" src="" alt="Foto OLT" class="max-h-[90vh] max-w-full rounded-2xl object-contain shadow-2xl" onclick="event.stopPropagation()">
</div>
@endsection

@push('scripts')
<script>
function openFullscreen(source) {
    document.getElementById('fullscreenImage').src = typeof source === 'string' ? source : source.src;
    document.getElementById('fullscreenModal').classList.remove('hidden');
    document.getElementById('fullscreenModal').classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeFullscreen() {
    document.getElementById('fullscreenModal').classList.add('hidden');
    document.getElementById('fullscreenModal').classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closeFullscreen();
});

document.addEventListener('DOMContentLoaded', function() {
    var oltLat = @json($olt->latitude);
    var oltLng = @json($olt->longitude);
    var popLat = @json($olt->pop->latitude ?? null);
    var popLng = @json($olt->pop->longitude ?? null);

    var map = L.map('map');

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Custom icons
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

    var markers = [];

    // Add OLT marker
    if (oltLat && oltLng) {
        var oltMarker = L.marker([oltLat, oltLng], { icon: oltIcon }).addTo(map);
        oltMarker.bindPopup('<b>OLT: {{ $olt->nama_olt }}</b><br>{{ $olt->kode_olt }}').openPopup();
        markers.push([oltLat, oltLng]);
    }

    // Add POP marker
    if (popLat && popLng) {
        var popMarker = L.marker([popLat, popLng], { icon: popIcon }).addTo(map);
        popMarker.bindPopup('<b>POP: {{ $olt->pop->nama_pop ?? "POP" }}</b><br>{{ $olt->pop->kode_pop ?? "" }}').openPopup();
        markers.push([popLat, popLng]);
    }

    // Calculate route if both coordinates exist
    if (oltLat && oltLng && popLat && popLng) {
        if (window.L && L.Routing && typeof L.Routing.control === 'function') {
            L.Routing.control({
                waypoints: [
                    L.latLng(popLat, popLng),
                    L.latLng(oltLat, oltLng)
                ],
                router: L.Routing.osrmv1({
                    serviceUrl: 'https://router.project-osrm.org/route/v1'
                }),
                lineOptions: {
                    styles: [{ color: '#f97316', weight: 5, opacity: 0.7 }]
                },
                createMarker: function() { return null; },
                show: false,
                addWaypoints: false
            }).addTo(map);
        } else {
            L.polyline([[popLat, popLng], [oltLat, oltLng]], {
                color: '#f97316',
                weight: 5,
                opacity: 0.7,
                dashArray: '8 8'
            }).addTo(map);
        }

        map.fitBounds([
            [oltLat, oltLng],
            [popLat, popLng]
        ], { padding: [50, 50] });
    } else if (markers.length > 0) {
        map.setView(markers[0], 15);
    } else {
        map.setView([-2.5, 118.0], 5);
    }

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

                            searchMarker = L.marker([newLat, newLng]).addTo(map);
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
});
</script>
@endpush
