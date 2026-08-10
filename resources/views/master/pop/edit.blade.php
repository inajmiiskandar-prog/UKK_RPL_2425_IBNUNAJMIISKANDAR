@extends('layouts.dashboard')

@section('title', 'Edit POP')
@section('page-title', 'Edit POP')
@section('page-breadcrumb', 'Master Data / POP / Edit')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

<div class="mb-6">
    <a href="{{ route('masterdata.pop.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h2 class="mb-6 font-display text-lg font-semibold text-gray-800 dark:text-white">Form Edit POP</h2>

    @if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">Terjadi kesalahan:</p>
        <ul class="mt-1 list-inside list-disc text-sm text-red-600 dark:text-red-300">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('masterdata.pop.update', $pop->id_pop) }}" method="POST" class="space-y-5">
        @csrf @method('PUT')
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="kode_pop" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Kode POP</label>
                <input type="text" id="kode_pop" value="{{ $pop->kode_pop }}" disabled class="w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 py-3 text-sm text-gray-500 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-400">
            </div>
            <div>
                <label for="nama_pop" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Nama POP <span class="text-red-500">*</span></label>
                <input type="text" id="nama_pop" name="nama_pop" value="{{ old('nama_pop', $pop->nama_pop) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="id_area" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Area <span class="text-red-500">*</span></label>
                <select id="id_area" name="id_area" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    @foreach($areas as $area)
                    <option value="{{ $area->id_area }}" {{ $pop->id_area == $area->id_area ? 'selected' : '' }}>{{ $area->kode_area }} - {{ $area->nama_area }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label for="alamat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Alamat <span class="text-red-500">*</span></label>
            <textarea id="alamat" name="alamat" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>{{ old('alamat', $pop->alamat) }}</textarea>
        </div>

        {{-- Peta Interaktif --}}
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                Lokasi (Klik atau geser marker pada peta)
            </label>
            <div id="map" class="mb-3 h-96 w-full rounded-xl border border-gray-200 dark:border-slate-600"></div>
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="latitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Latitude</label>
                    <input type="text" id="latitude" name="latitude" value="{{ old('latitude', $pop->latitude) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" readonly>
                </div>
                <div>
                    <label for="longitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Longitude</label>
                    <input type="text" id="longitude" name="longitude" value="{{ old('longitude', $pop->longitude) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" readonly>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">💡 Klik pada peta atau geser marker untuk menentukan lokasi</p>
        </div>

        <div class="flex items-center gap-3 pt-4">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700 hover:shadow-xl">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Update
            </button>
            <a href="{{ route('masterdata.pop.index') }}" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">Batal</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initial coordinates from existing data or default to Indonesia
    var lat = {{ old('latitude', $pop->latitude ?? '-2.5') }};
    var lng = {{ old('longitude', $pop->longitude ?? '118.0') }};

    var map = L.map('map').setView([lat, lng], 13);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Create draggable marker
    var marker = L.marker([lat, lng], {
        draggable: true
    }).addTo(map);

    // Add search/geocoder control
    L.Control.geocoder({
        defaultMarkGeocode: true
    }).on('markgeocode', function(e) {
        var center = e.geocode.center;
        marker.setLatLng(center);
        map.setView(center, 16);
        document.getElementById('latitude').value = center.lat.toFixed(6);
        document.getElementById('longitude').value = center.lng.toFixed(6);
    }).addTo(map);

    // Update input fields on marker drag
    function updateInputs() {
        var pos = marker.getLatLng();
        document.getElementById('latitude').value = pos.lat.toFixed(6);
        document.getElementById('longitude').value = pos.lng.toFixed(6);
    }

    marker.on('dragend', updateInputs);

    // Click on map to move marker
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateInputs();
    });

    // Initialize with existing coordinates if available
    @if($pop->latitude && $pop->longitude)
        map.setView([lat, lng], 15);
        updateInputs();
    @else
        map.setView([-2.5, 118.0], 5);
    @endif
});
</script>
@endsection
