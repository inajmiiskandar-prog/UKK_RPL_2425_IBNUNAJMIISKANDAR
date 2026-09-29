@extends('layouts.dashboard')

@section('title', 'Tambah OLT')
@section('page-title', 'Tambah OLT')
@section('page-breadcrumb', 'Master Data / OLT / Tambah')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

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

    <form action="{{ route('masterdata.olt.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="nama_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Nama OLT <span class="text-red-500">*</span></label>
                <input type="text" id="nama_olt" name="nama_olt" value="{{ old('nama_olt') }}" placeholder="Masukkan nama OLT" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
            <div>
                <label for="id_pop" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">POP <span class="text-red-500">*</span></label>
                <select id="id_pop" name="id_pop" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white" required onchange="updateCoordsFromPop()">
                    <option value="">Pilih POP</option>
                    @foreach($pops as $pop)
                    <option value="{{ $pop->id_pop }}" data-lat="{{ $pop->latitude }}" data-lng="{{ $pop->longitude }}" data-lokasi="{{ $pop->lokasi }}" {{ old('id_pop') == $pop->id_pop ? 'selected' : '' }}>{{ $pop->kode_pop }} - {{ $pop->nama_pop }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="ip_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">IP Address</label>
                <input type="text" id="ip_olt" name="ip_olt" value="{{ old('ip_olt') }}" placeholder="Contoh: 192.168.1.1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
            <div>
                <label for="lokasi" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Lokasi</label>
                <input type="text" id="lokasi" name="lokasi" value="{{ old('lokasi') }}" placeholder="Akan auto-fill dari POP" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="username_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Username</label>
                <input type="text" id="username_olt" name="username_olt" value="{{ old('username_olt') }}" placeholder="Username OLT" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
            <div>
                <label for="password_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Password</label>
                <input type="password" id="password_olt" name="password_olt" placeholder="Password OLT" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
        </div>

        <div>
            <label for="foto_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Foto OLT</label>
            <input type="file" id="foto_olt" name="foto_olt" accept="image/*" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-purple-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-purple-600 hover:file:bg-purple-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:file:bg-purple-900/30 dark:file:text-purple-400">
            <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG. Maksimal 2MB</p>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Lokasi</label>
            <div id="map" class="h-80 w-full rounded-xl border border-gray-200 dark:border-slate-600"></div>
            <div class="flex items-center gap-2 mt-2">
                <button type="button" id="btn-gps" class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    GPS Saya
                </button>
                <span class="text-xs text-gray-500">Klik peta atau cari alamat untuk memilih lokasi</span>
            </div>
            <div class="grid gap-5 md:grid-cols-2 mt-3">
                <div>
                    <label for="latitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Latitude</label>
                    <input type="text" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="-6.208763" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white" readonly>
                </div>
                <div>
                    <label for="longitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Longitude</label>
                    <input type="text" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="106.845599" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white" readonly>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-4">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan
            </button>
            <a href="{{ route('masterdata.olt.index') }}" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">Batal</a>
        </div>
    </form>
</div>

<script>
var map, marker;

document.addEventListener('DOMContentLoaded', function() {
    var lat = {{ old('latitude') ? old('latitude') : '-6.2' }};
    var lng = {{ old('longitude') ? old('longitude') : '106.8' }};

    map = L.map('map').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    marker = L.marker([lat, lng], { draggable: true }).addTo(map);

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

    document.getElementById('btn-gps').addEventListener('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var pos = [position.coords.latitude, position.coords.longitude];
                marker.setLatLng(pos);
                map.setView(pos, 16);
                document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
                document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
            }, function() {
                alert('Tidak bisa mendapatkan lokasi GPS');
            });
        } else {
            alert('Browser tidak mendukung GPS');
        }
    });

    if (lat && lng) {
        updateInputs();
    }
});

function updateCoordsFromPop() {
    var select = document.getElementById('id_pop');
    var option = select.options[select.selectedIndex];
    var lat = option.getAttribute('data-lat');
    var lng = option.getAttribute('data-lng');
    var lokasi = option.getAttribute('data-lokasi');

    var lokasiInput = document.getElementById('lokasi');
    if (!lokasiInput.value && lokasi) {
        lokasiInput.value = lokasi;
    }

    if (lat && lng) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], 16);
    }
}
</script>
@endsection
