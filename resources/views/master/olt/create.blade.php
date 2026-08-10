@extends('layouts.dashboard')

@section('title', 'Tambah OLT')
@section('page-title', 'Tambah OLT')
@section('page-breadcrumb', 'Master Data / OLT / Tambah')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

<div class="mb-6">
    <a href="{{ route('masterdata.olt.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h2 class="mb-6 font-display text-lg font-semibold text-gray-800 dark:text-white">Form Tambah OLT</h2>

    @if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">Terjadi kesalahan:</p>
        <ul class="mt-1 list-inside list-disc text-sm text-red-600 dark:text-red-300">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('masterdata.olt.store') }}" method="POST" class="space-y-5">
        @csrf
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="nama_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Nama OLT <span class="text-red-500">*</span></label>
                <input type="text" id="nama_olt" name="nama_olt" value="{{ old('nama_olt') }}" placeholder="Masukkan nama OLT" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
            <div>
                <label for="id_pop" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">POP <span class="text-red-500">*</span></label>
                <select id="id_pop" name="id_pop" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required onchange="updateCoordsFromPop()">
                    <option value="">Pilih POP</option>
                    @foreach($pops as $pop)
                    <option value="{{ $pop->id_pop }}" data-lat="{{ $pop->latitude }}" data-lng="{{ $pop->longitude }}" {{ old('id_pop') == $pop->id_pop ? 'selected' : '' }}>{{ $pop->kode_pop }} - {{ $pop->nama_pop }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label for="lokasi" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Lokasi <span class="text-red-500">*</span></label>
            <input type="text" id="lokasi" name="lokasi" value="{{ old('lokasi') }}" placeholder="Masukkan lokasi OLT" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
        </div>

        {{-- Peta Interaktif --}}
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                Lokasi (Klik atau geser marker pada peta, atau cari lokasi)
            </label>
            <div id="map" class="mb-3 h-80 w-full rounded-xl border border-gray-200 dark:border-slate-600"></div>
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="latitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Latitude</label>
                    <input type="text" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="Contoh: -6.208763" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" readonly>
                </div>
                <div>
                    <label for="longitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Longitude</label>
                    <input type="text" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="Contoh: 106.845599" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" readonly>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">💡 Pilih POP untuk auto-fill koordinat, atau klik/geser marker untuk adjust</p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="ip_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">IP Address</label>
                <input type="text" id="ip_olt" name="ip_olt" value="{{ old('ip_olt') }}" placeholder="Contoh: 192.168.1.1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
            <div>
                <label for="username_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Username</label>
                <input type="text" id="username_olt" name="username_olt" value="{{ old('username_olt') }}" placeholder="Username OLT" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
        </div>
        <div>
            <label for="password_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Password</label>
            <input type="password" id="password_olt" name="password_olt" placeholder="Password OLT" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
        </div>
        <div class="flex items-center gap-3 pt-4">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700 hover:shadow-xl">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan
            </button>
            <a href="{{ route('masterdata.olt.index') }}" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">Batal</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var lat = {{ old('latitude') ? old('latitude') : '-2.5' }};
    var lng = {{ old('longitude') ? old('longitude') : '118.0' }};

    var map = L.map('map').setView([lat, lng], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    // Add search control
    L.Control.geocoder({
        defaultMarkGeocode: true
    }).on('markgeocode', function(e) {
        var center = e.geocode.center;
        marker.setLatLng(center);
        map.setView(center, 16);
        document.getElementById('latitude').value = center.lat.toFixed(6);
        document.getElementById('longitude').value = center.lng.toFixed(6);
    }).addTo(map);

    function updateInputs() {
        var pos = marker.getLatLng();
        document.getElementById('latitude').value = pos.lat.toFixed(6);
        document.getElementById('longitude').value = pos.lng.toFixed(6);
    }

    marker.on('dragend', updateInputs);

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateInputs();
    });

    if (lat && lng && lat != -2.5) {
        map.setView([lat, lng], 15);
        updateInputs();
    }
});

// Auto update coords when POP is selected
function updateCoordsFromPop() {
    var select = document.getElementById('id_pop');
    var option = select.options[select.selectedIndex];
    var lat = option.getAttribute('data-lat');
    var lng = option.getAttribute('data-lng');

    if (lat && lng) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        var map = L.map('map');
        map.setView([lat, lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        L.Control.geocoder({ defaultMarkGeocode: true }).addTo(map);

        L.marker([lat, lng], { draggable: true }).addTo(map);
    }
}
</script>
@endsection
