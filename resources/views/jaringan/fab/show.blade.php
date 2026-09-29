@extends('layouts.dashboard')

@section('title', 'Detail Pelanggan')
@section('page-title', 'Detail Pelanggan')
@section('page-breadcrumb', 'Jaringan / Pelanggan / Detail')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('jaringan.fab.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali
    </a>
    <a href="{{ route('jaringan.fab.edit', $fab->id_fab) }}" class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Edit
    </a>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-6 flex items-center gap-4">
            @if($fab->foto)
            <img src="{{ Storage::url($fab->foto) }}" alt="Foto {{ $fab->nama_pelanggan }}" class="h-20 w-20 cursor-pointer rounded-full border-2 border-purple-200 object-cover shadow-sm transition hover:scale-105 dark:border-slate-600" onclick="openFabPhoto(this)">
            @else
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-pink-500 text-2xl font-bold text-white shadow-sm">
                {{ substr($fab->nama_pelanggan, 0, 1) }}
            </div>
            @endif
            <div>
                <h2 class="font-display text-xl font-bold text-gray-800 dark:text-white">{{ $fab->nama_pelanggan }}</h2>
                <span class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold {{ $fab->status == 'AKTIF' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                    {{ $fab->status }}
                </span>
            </div>
        </div>
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Kode FAB</p><p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">{{ $fab->kode_fab }}</p></div>
                <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">NIK</p><p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">{{ $fab->nik }}</p></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">No. HP</p><p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $fab->no_hp }}</p></div>
                <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Area</p><p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $fab->area->nama_area ?? '-' }}</p></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Paket</p><p class="mt-1 text-sm font-semibold text-purple-600 dark:text-purple-400">{{ $fab->paket->nama_paket ?? '-' }}</p></div>
                <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Sales</p><p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $fab->sales->nama ?? '-' }}</p></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Latitude</p><p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $fab->latitude ?: '-' }}</p></div>
                <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Longitude</p><p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $fab->longitude ?: '-' }}</p></div>
            </div>
            <div class="pt-4 border-t border-gray-100 dark:border-slate-700"><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Alamat</p><p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $fab->alamat }}</p></div>
        </div>
    </div>
    @if($fab->latitude && $fab->longitude)
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-display text-lg font-semibold text-gray-800 dark:text-white">Lokasi Pelanggan</h3>
        <div id="show_map" class="h-80 w-full rounded-xl border border-gray-200 dark:border-slate-600"></div>
    </div>
    @endif
</div>

<div id="fabPhotoModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/80 p-4" onclick="closeFabPhoto()">
    <button type="button" class="absolute right-4 top-4 rounded-full bg-white/15 p-2 text-white transition hover:bg-white/30" aria-label="Tutup foto">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <img id="fabPhotoPreview" src="" alt="Foto pelanggan" class="max-h-[90vh] max-w-full rounded-2xl object-contain shadow-2xl" onclick="event.stopPropagation()">
</div>

<script>
function openFabPhoto(image) {
    document.getElementById('fabPhotoPreview').src = image.src;
    document.getElementById('fabPhotoModal').classList.remove('hidden');
    document.getElementById('fabPhotoModal').classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeFabPhoto() {
    document.getElementById('fabPhotoModal').classList.add('hidden');
    document.getElementById('fabPhotoModal').classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closeFabPhoto();
});

document.addEventListener('DOMContentLoaded', function() {
    @if($fab->latitude && $fab->longitude)
    var map = L.map('show_map').setView([{{ $fab->latitude }}, {{ $fab->longitude }}], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    L.marker([{{ $fab->latitude }}, {{ $fab->longitude }}]).addTo(map)
        .bindPopup('{{ $fab->nama_pelanggan }}')
        .openPopup();
    @endif
});
</script>
@endsection
