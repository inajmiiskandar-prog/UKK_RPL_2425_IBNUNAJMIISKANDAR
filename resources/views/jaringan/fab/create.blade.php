@extends('layouts.dashboard')

@section('title', 'Tambah Pelanggan')
@section('page-title', 'Tambah Pelanggan')
@section('page-breadcrumb', 'Jaringan / Pelanggan / Tambah')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<div class="mb-6">
    <a href="{{ route('jaringan.fab.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h2 class="mb-6 font-display text-lg font-semibold text-gray-800 dark:text-white">Form Tambah Pelanggan</h2>

    @if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">Terjadi kesalahan:</p>
        <ul class="mt-1 list-inside list-disc text-sm text-red-600 dark:text-red-300">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('jaringan.fab.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div class="grid gap-5 md:grid-cols-3">
            <div>
                <label for="tanggal_daftar" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Tanggal <span class="text-red-500">*</span></label>
                <input type="text" id="tanggal_daftar" value="{{ date('d M Y') }}" readonly class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-slate-500 focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300" />
                <input type="hidden" name="tanggal_daftar" value="{{ date('Y-m-d') }}" />
            </div>
            <div>
                <label for="nama_pelanggan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Nama Pelanggan <span class="text-red-500">*</span></label>
                <input type="text" id="nama_pelanggan" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}" placeholder="Nama lengkap" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
            <div>
                <label for="nik" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">NIK <span class="text-red-500">*</span></label>
                <input type="text" id="nik" name="nik" value="{{ old('nik') }}" placeholder="Nomor KTP" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="no_hp" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">No. HP <span class="text-red-500">*</span></label>
                <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
            <div>
                <label for="id_area" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Area <span class="text-red-500">*</span></label>
                <select id="id_area" name="id_area" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">Pilih Area</option>
                    @foreach($areas as $area)
                    <option value="{{ $area->id_area }}" {{ old('id_area') == $area->id_area ? 'selected' : '' }}>{{ $area->nama_area }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="id_paket" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Paket <span class="text-red-500">*</span></label>
                <select id="id_paket" name="id_paket" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">Pilih Paket</option>
                    @foreach($pakets as $paket)
                    <option value="{{ $paket->id_paket }}" {{ old('id_paket') == $paket->id_paket ? 'selected' : '' }}>{{ $paket->nama_paket }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="OPEN" {{ old('status') == 'OPEN' ? 'selected' : '' }}>Open (Belum Aktif)</option>
                    <option value="AKTIF" {{ old('status') == 'AKTIF' ? 'selected' : '' }}>Aktif</option>
                </select>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="id_user" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Sales</label>
                <select id="id_user" name="id_user" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    <option value="">Pilih Sales</option>
                    @foreach($sales as $s)
                    <option value="{{ $s->id_user }}" {{
                        auth()->user()->role === 'SALES'
                            ? ($s->id_user == auth()->id() ? 'selected' : '')
                            : (old('id_user') == $s->id_user ? 'selected' : '')
                    }}>{{ $s->nama }}</option>
                    @endforeach
                </select>
                @if(auth()->user()->role === 'SALES')
                <p class="mt-1 text-xs text-green-600 font-medium">✓ Otomatis dipilih berdasarkan akun login</p>
                @endif
            </div>
        </div>

        <div>
            <label for="alamat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Alamat <span class="text-red-500">*</span></label>
            <textarea id="alamat" name="alamat" rows="2" placeholder="Alamat lengkap" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>{{ old('alamat') }}</textarea>
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            <div>
                <label for="latitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Latitude</label>
                <input type="text" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="-6.2087634" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
            <div>
                <label for="longitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Longitude</label>
                <input type="text" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="106.845599" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
            <div>
                <label for="foto" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Foto</label>
                <input type="file" id="foto" name="foto" accept="image/*" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-purple-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-purple-600 hover:file:bg-purple-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:file:bg-purple-900/30 dark:file:text-purple-400">
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Lokasi</label>
            <div id="map_preview" class="h-80 w-full rounded-xl border border-gray-200 bg-gray-100 dark:border-slate-600 dark:bg-slate-700"></div>
            <div class="flex items-center gap-2 mt-2">
                <button type="button" id="btn-gps" class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    GPS Saya
                </button>
                <span class="text-xs text-gray-500">Klik peta atau cari alamat untuk memilih lokasi</span>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-4">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan
            </button>
            <a href="{{ route('jaringan.fab.index') }}" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">Batal</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var lat = {{ old('latitude') ? old('latitude') : '-6.2' }};
    var lng = {{ old('longitude') ? old('longitude') : '106.8' }};

    var map = L.map('map_preview').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    // Search/Geocoder
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

    // GPS Button
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
});
</script>
@endsection
