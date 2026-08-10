@extends('layouts.dashboard')

@section('title', 'Edit OLT')
@section('page-title', 'Edit OLT')
@section('page-breadcrumb', 'Master Data / OLT / Edit')

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
    <h2 class="mb-6 font-display text-lg font-semibold text-gray-800 dark:text-white">Form Edit OLT</h2>

    @if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">Terjadi kesalahan:</p>
        <ul class="mt-1 list-inside list-disc text-sm text-red-600 dark:text-red-300">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('masterdata.olt.update', $olt->id_olt) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="kode_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Kode OLT</label>
                <input type="text" id="kode_olt" value="{{ $olt->kode_olt }}" disabled class="w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 py-3 text-sm text-gray-500 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-400">
            </div>
            <div>
                <label for="nama_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Nama OLT <span class="text-red-500">*</span></label>
                <input type="text" id="nama_olt" name="nama_olt" value="{{ old('nama_olt', $olt->nama_olt) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
        </div>

        {{-- Info & Credential Section --}}
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-slate-700 dark:bg-slate-700/50">
            <h4 class="mb-3 font-semibold text-gray-700 dark:text-gray-200">Info & Credential</h4>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="id_pop" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">POP <span class="text-red-500">*</span></label>
                    <select id="id_pop" name="id_pop" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required onchange="updateCoordsFromPop()">
                        @foreach($pops as $pop)
                        <option value="{{ $pop->id_pop }}" data-lat="{{ $pop->latitude }}" data-lng="{{ $pop->longitude }}" data-lokasi="{{ $pop->lokasi }}" {{ $olt->id_pop == $pop->id_pop ? 'selected' : '' }}>{{ $pop->kode_pop }} - {{ $pop->nama_pop }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="lokasi" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Lokasi</label>
                    <input type="text" id="lokasi" name="lokasi" value="{{ old('lokasi', $olt->lokasi) }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
                <div>
                    <label for="ip_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">IP Address</label>
                    <input type="text" id="ip_olt" name="ip_olt" value="{{ old('ip_olt', $olt->ip_olt) }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
                <div>
                    <label for="username_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Username</label>
                    <input type="text" id="username_olt" name="username_olt" value="{{ old('username_olt', $olt->username_olt) }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="password_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Password</label>
                    <input type="password" id="password_olt" name="password_olt" placeholder="Kosongkan jika tidak diubah" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
                <div>
                    <label for="foto_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Foto OLT</label>
                    @if($olt->foto_olt)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $olt->foto_olt) }}" alt="Foto OLT" class="h-16 w-auto rounded-lg border border-gray-200 dark:border-slate-600">
                        <span class="ml-2 text-xs text-gray-500">Foto saat ini</span>
                    </div>
                    @endif
                    <input type="file" id="foto_olt" name="foto_olt" accept="image/*" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-purple-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-purple-700 hover:file:bg-purple-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:file:bg-purple-900 dark:file:text-purple-300">
                    <p class="mt-1 text-xs text-gray-500">Format: jpeg, png, jpg, gif, svg. Maksimal 2MB</p>
                </div>
            </div>
        </div>

        {{-- Peta Interaktif (Bottom) --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
            <h4 class="mb-3 font-semibold text-gray-700 dark:text-gray-200">Lokasi Pada Peta</h4>
            <div id="map" class="mb-3 h-80 w-full rounded-xl border border-gray-200 dark:border-slate-600"></div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="latitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Latitude</label>
                    <input type="text" id="latitude" name="latitude" value="{{ old('latitude', $olt->latitude) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" readonly>
                </div>
                <div>
                    <label for="longitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Longitude</label>
                    <input type="text" id="longitude" name="longitude" value="{{ old('longitude', $olt->longitude) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" readonly>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">💡 Klik pada peta atau geser marker untuk menentukan lokasi</p>
        </div>

        <div class="flex items-center gap-3 pt-4">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700 hover:shadow-xl">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Update
            </button>
            <a href="{{ route('masterdata.olt.index') }}" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">Batal</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var lat = {{ old('latitude', $olt->latitude ?? '-2.5') }};
    var lng = {{ old('longitude', $olt->longitude ?? '118.0') }};

    var map = L.map('map').setView([lat, lng], {{ $olt->latitude ? '15' : '5' }});

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

    @if($olt->latitude && $olt->longitude)
        updateInputs();
    @endif
});

// Auto update coords and lokasi when POP is selected
function updateCoordsFromPop() {
    var select = document.getElementById('id_pop');
    var option = select.options[select.selectedIndex];
    var lat = option.getAttribute('data-lat');
    var lng = option.getAttribute('data-lng');
    var lokasi = option.getAttribute('data-lokasi');

    // Auto-fill lokasi from POP if empty
    var lokasiInput = document.getElementById('lokasi');
    if (!lokasiInput.value && lokasi) {
        lokasiInput.value = lokasi;
    }

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
