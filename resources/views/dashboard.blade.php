@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-breadcrumb', 'Dashboard / Beranda')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-4px);
    }
    .activity-item {
        transition: background-color 0.2s;
    }
    .activity-item:hover {
        background-color: #f8fafc;
    }
    .dark .activity-item:hover {
        background-color: #1e293b;
    }
    #map {
        height: min(400px, 65vh);
        min-height: 280px;
        border-radius: 1rem;
        position: relative;
        z-index: 0 !important;
        isolation: isolate;
    }
    .leaflet-container {
        z-index: 0 !important;
    }
    @media (max-width: 640px) {
        #map {
            height: 320px;
        }
    }
    .leaflet-popup-content-wrapper {
        border-radius: 0.75rem;
    }
    .map-filter-btn.active {
        background-color: #9333ea;
        color: white;
    }
</style>
@endpush

@section('content')
{{-- Statistics Cards --}}
<div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-6">
    {{-- Total Pelanggan --}}
    <div class="stat-card rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pelanggan</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($stats['total_pelanggan']) }}</p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        {{ $stats['total_pelanggan_aktif'] }} Aktif
                    </span>
                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                        {{ $stats['total_pelanggan_open'] }} Open
                    </span>
                </div>
            </div>
            <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/30">
                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Total Area --}}
    <div class="stat-card rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Area</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($stats['total_area']) }}</p>
                <p class="mt-2 text-xs text-gray-400">{{ $stats['total_pop'] }} POP</p>
            </div>
            <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/30">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Total OLT --}}
    <div class="stat-card rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">OLT</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($stats['total_olt']) }}</p>
                <p class="mt-2 text-xs text-gray-400">{{ $stats['total_odp'] }} ODP</p>
            </div>
            <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/30">
                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Total ONT --}}
    <div class="stat-card rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ONT</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($stats['total_ont']) }}</p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                        {{ $ont_status['tersedia'] }} Tersedia
                    </span>
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        {{ $ont_status['terpasang'] }} Terpasang
                    </span>
                </div>
            </div>
            <div class="rounded-full bg-orange-100 p-3 dark:bg-orange-900/30">
                <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- Second Row Statistics --}}
<div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-6">
    {{-- Port PON --}}
    <div class="stat-card rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Port PON</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($stats['total_portpon']) }}</p>
                <div class="mt-2 flex items-center gap-1">
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        {{ $port_status['tersedia'] }} Free
                    </span>
                </div>
            </div>
            <div class="rounded-full bg-cyan-100 p-3 dark:bg-cyan-900/30">
                <svg class="h-6 w-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- BAA / Instalasi --}}
    <div class="stat-card rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">BAA</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($stats['total_baa']) }}</p>
                <p class="mt-2 text-xs text-gray-400">Total Instalasi</p>
            </div>
            <div class="rounded-full bg-pink-100 p-3 dark:bg-pink-900/30">
                <svg class="h-6 w-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Paket --}}
    <div class="stat-card rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Paket</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($stats['total_paket']) }}</p>
                <p class="mt-2 text-xs text-gray-400">{{ $stats['total_material'] }} Material</p>
            </div>
            <div class="rounded-full bg-indigo-100 p-3 dark:bg-indigo-900/30">
                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Users --}}
    <div class="stat-card rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Users</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($stats['total_user']) }}</p>
                <p class="mt-2 text-xs text-gray-400">User Aktif</p>
            </div>
            <div class="rounded-full bg-teal-100 p-3 dark:bg-teal-900/30">
                <svg class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- Content Row --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Recent Customers --}}
    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-slate-700">
            <h3 class="font-display text-sm font-semibold text-gray-800 dark:text-white">Pelanggan Terbaru</h3>
            <a href="{{ route('jaringan.fab.index') }}" class="text-xs font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400">Lihat Semua</a>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-slate-700">
            @forelse($recent_customers as $customer)
            <div class="flex items-center gap-3 px-5 py-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-pink-500 text-white text-xs font-bold">
                    {{ substr($customer->nama_pelanggan, 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-gray-800 dark:text-white">{{ $customer->nama_pelanggan }}</p>
                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $customer->area->nama_area ?? '-' }} | {{ $customer->paket->nama_paket ?? '-' }}</p>
                </div>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $customer->status == 'AKTIF' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                    {{ $customer->status }}
                </span>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Belum ada data pelanggan
            </div>
            @endforelse
        </div>
    </div>

    {{-- Recent Installations --}}
    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-slate-700">
            <h3 class="font-display text-sm font-semibold text-gray-800 dark:text-white">Instalasi Terbaru</h3>
            <a href="{{ route('jaringan.baa.index') }}" class="text-xs font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400">Lihat Semua</a>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-slate-700">
            @forelse($recent_installations as $install)
            <div class="px-5 py-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $install->fab->nama_pelanggan ?? '-' }}</p>
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        {{ $install->status }}
                    </span>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $install->createdAt->format('d M Y') }} | Teknisi: {{ $install->teknisi->nama ?? '-' }}
                </p>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Belum ada data instalasi
            </div>
            @endforelse
        </div>
    </div>

    {{-- Low Stock Materials --}}
    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-slate-700">
            <h3 class="font-display text-sm font-semibold text-gray-800 dark:text-white">Material Habis</h3>
            <a href="{{ route('masterdata.material.index') }}" class="text-xs font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400">Lihat Semua</a>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-slate-700">
            @forelse($low_stock_materials as $material)
            <div class="flex items-center justify-between px-5 py-3">
                <div>
                    <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $material->nama_material }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $material->kode_material }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-red-600 dark:text-red-400">{{ $material->stok }} {{ $material->satuan }}</p>
                    <p class="text-xs text-gray-400">Min: {{ $material->minimal_stok }}</p>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Stok material aman
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- FAB & BAA Monthly Chart Section --}}
<div class="mt-4 rounded-xl border border-gray-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2 dark:border-slate-700">
        <div class="flex items-center gap-2">
            <div class="flex -space-x-1">
                <div class="rounded-full bg-purple-100 p-1.5 dark:bg-purple-900/50">
                    <svg class="h-3 w-3 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="rounded-full bg-pink-100 p-1.5 dark:bg-pink-900/50">
                    <svg class="h-3 w-3 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-xs font-semibold text-gray-800 dark:text-white">Statistik FAB & BAA - 6 Bulan Terakhir</h3>
        </div>
        <div class="flex items-center gap-3 text-xs">
            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-purple-500"></span> FAB</span>
            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-pink-500"></span> BAA</span>
        </div>
    </div>
    <div class="p-3">
        <canvas id="fabBaaChart" height="80"></canvas>
    </div>
</div>

{{-- SLA FAB Pending --}}
<div class="mt-6 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700">
        <div>
            <div class="flex items-center gap-2">
                <div class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900/30">
                    <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-display text-sm font-semibold text-gray-800 dark:text-white">SLA FAB Pending</h3>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pelanggan berstatus OPEN yang menunggu proses instalasi</p>
        </div>
        <div class="flex items-center gap-2 text-xs">
            <span class="rounded-full bg-blue-100 px-2.5 py-1 font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ $pending_fab_count }} Pending</span>
            <a href="{{ route('jaringan.fab.index', ['status' => 'OPEN']) }}" class="font-medium text-purple-600 hover:underline dark:text-purple-400">Lihat semua</a>
        </div>
    </div>
    <div class="divide-y divide-gray-50 dark:divide-slate-700">
        @forelse($pending_fabs as $pendingFab)
            @php
                $pendingHours = $pendingFab->createdAt ? $pendingFab->createdAt->diffInHours(now()) : 0;
                $slaClass = $pendingHours >= 24
                    ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
                    : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
                $slaLabel = $pendingHours >= 24 ? 'Perhatian' : 'Sesuai SLA';
            @endphp
            <a href="{{ route('jaringan.fab.show', $pendingFab->id_fab) }}" class="flex flex-col gap-2 px-5 py-3 transition hover:bg-gray-50 sm:flex-row sm:items-center sm:justify-between dark:hover:bg-slate-700/50">
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-gray-800 dark:text-white">{{ $pendingFab->nama_pelanggan }}</p>
                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $pendingFab->kode_fab }} &middot; {{ $pendingFab->area->nama_area ?? '-' }} &middot; {{ $pendingFab->paket->nama_paket ?? '-' }}</p>
                </div>
                <div class="flex items-center gap-3 sm:flex-shrink-0">
                    <span class="text-xs text-gray-400">{{ $pendingHours }} jam</span>
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $slaClass }}">{{ $slaLabel }}</span>
                </div>
            </a>
        @empty
            <div class="px-5 py-8 text-center text-sm text-green-600 dark:text-green-400">Tidak ada FAB pending. Semua sudah diproses.</div>
        @endforelse
    </div>
</div>

{{-- Map Section --}}
<div class="mt-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-lg font-semibold text-gray-800 dark:text-white">Peta Jaringan</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Lokasi POP, OLT, ODP, dan Pelanggan</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="toggleLayer('pop')" id="btn-pop" class="map-filter-btn active rounded-lg px-3 py-1.5 text-xs font-medium border border-purple-600 text-purple-600 dark:border-purple-400 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/30">
                POP ({{ $mapData['pops']->count() }})
            </button>
            <button onclick="toggleLayer('olt')" id="btn-olt" class="map-filter-btn active rounded-lg px-3 py-1.5 text-xs font-medium border border-green-600 text-green-600 dark:border-green-400 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30">
                OLT ({{ $mapData['olts']->count() }})
            </button>
            <button onclick="toggleLayer('odp')" id="btn-odp" class="map-filter-btn active rounded-lg px-3 py-1.5 text-xs font-medium border border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30">
                ODP ({{ $mapData['odps']->count() }})
            </button>
            <button onclick="toggleLayer('fab')" id="btn-fab" class="map-filter-btn active rounded-lg px-3 py-1.5 text-xs font-medium border border-pink-600 text-pink-600 dark:border-pink-400 dark:text-pink-400 hover:bg-pink-50 dark:hover:bg-pink-900/30">
                Pelanggan ({{ $mapData['fabs']->count() }})
            </button>
        </div>
    </div>
    <div id="map"></div>
</div>

{{-- Recent Activity Log --}}
<div class="mt-6 rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-slate-700">
        <h3 class="font-display text-sm font-semibold text-gray-800 dark:text-white">Aktivitas Terbaru</h3>
    </div>
    <div class="divide-y divide-gray-50 dark:divide-slate-700">
        @forelse($recent_activity as $activity)
        <div class="activity-item flex items-start gap-3 px-5 py-3">
            <div class="mt-0.5 rounded-full bg-purple-100 p-1.5 dark:bg-purple-900/30">
                <svg class="h-3.5 w-3.5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $activity->description }}</p>
                <div class="mt-1 flex items-center gap-2 text-xs text-gray-400">
                    <span>{{ $activity->user->nama ?? 'System' }}</span>
                    <span>&bull;</span>
                    <span>{{ $activity->createdAt->diffForHumans() }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
            Belum ada aktivitas
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // FAB & BAA Monthly Chart
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const now = new Date();
    const chartLabels = [], fabTotals = [], baaTotals = [];
    for (let i = 5; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        chartLabels.push(months[d.getMonth()] + ' ' + d.getFullYear());
        fabTotals.push(0);
        baaTotals.push(0);
    }
    @json($monthly_fab).forEach(item => {
        const idx = chartLabels.findIndex(l => l.includes(item.year + ''));
        if (idx !== -1) fabTotals[idx] = item.total;
    });
    @json($monthly_baa).forEach(item => {
        const idx = chartLabels.findIndex(l => l.includes(item.year + ''));
        if (idx !== -1) baaTotals[idx] = item.total;
    });

    new Chart(document.getElementById('fabBaaChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'FAB',
                    data: fabTotals,
                    borderColor: '#9333ea',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#9333ea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1.5,
                    pointRadius: 3,
                    pointHoverRadius: 5
                },
                {
                    label: 'BAA',
                    data: baaTotals,
                    borderColor: '#ec4899',
                    backgroundColor: 'rgba(236, 72, 153, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ec4899',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1.5,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 8,
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 11 },
                    cornerRadius: 6
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 10 } },
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: {
                    ticks: { font: { size: 10 } },
                    grid: { display: false }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
</script>
<script>
    // Map data from controller
    const mapData = {
        pops: {!! json_encode($mapData['pops']) !!},
        olts: {!! json_encode($mapData['olts']) !!},
        odps: {!! json_encode($mapData['odps']) !!},
        fabs: {!! json_encode($mapData['fabs']) !!}
    };

    // Initialize map
    const map = L.map('map').setView([-6.9, 107.6], 10);

    // Add tile layer (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Custom icons
    const icons = {
        pop: L.divIcon({
            html: '<div class="flex items-center justify-center w-8 h-8 bg-purple-600 rounded-full border-2 border-white shadow-lg"><svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg></div>',
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -16]
        }),
        olt: L.divIcon({
            html: '<div class="flex items-center justify-center w-8 h-8 bg-green-600 rounded-full border-2 border-white shadow-lg"><svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/></svg></div>',
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -16]
        }),
        odp: L.divIcon({
            html: '<div class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-full border-2 border-white shadow-lg"><svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/></svg></div>',
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -16]
        }),
        fab: L.divIcon({
            html: '<div class="flex items-center justify-center w-8 h-8 bg-pink-600 rounded-full border-2 border-white shadow-lg"><svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg></div>',
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -16]
        })
    };

    // Store marker layers
    const markerLayers = {
        pop: L.layerGroup(),
        olt: L.layerGroup(),
        odp: L.layerGroup(),
        fab: L.layerGroup()
    };

    // Add markers for each type
    function addMarkers() {
        // POP markers
        mapData.pops.forEach(item => {
            if (item.latitude && item.longitude) {
                const marker = L.marker([item.latitude, item.longitude], { icon: icons.pop })
                    .bindPopup('<div class="text-sm"><div class="font-bold text-purple-600">POP: ' + item.kode_pop + '</div><div class="text-gray-600">' + (item.nama_pop || '-') + '</div><div class="text-xs text-gray-400 mt-1">' + (item.alamat || '-') + '</div></div>');
                markerLayers.pop.addLayer(marker);
            }
        });

        // OLT markers
        mapData.olts.forEach(item => {
            if (item.latitude && item.longitude) {
                const marker = L.marker([item.latitude, item.longitude], { icon: icons.olt })
                    .bindPopup('<div class="text-sm"><div class="font-bold text-green-600">OLT: ' + item.kode_olt + '</div><div class="text-gray-600">' + (item.nama_olt || '-') + '</div><div class="text-xs text-gray-400 mt-1">' + (item.lokasi || '-') + '</div></div>');
                markerLayers.olt.addLayer(marker);
            }
        });

        // ODP markers
        mapData.odps.forEach(item => {
            if (item.latitude && item.longitude) {
                const marker = L.marker([item.latitude, item.longitude], { icon: icons.odp })
                    .bindPopup('<div class="text-sm"><div class="font-bold text-blue-600">ODP: ' + item.kode_odp + '</div><div class="text-gray-600">' + (item.nama_odp || '-') + '</div><div class="text-xs text-gray-400 mt-1">' + (item.alamat || '-') + '</div></div>');
                markerLayers.odp.addLayer(marker);
            }
        });

        // FAB markers
        mapData.fabs.forEach(item => {
            if (item.latitude && item.longitude) {
                const statusColor = item.status === 'AKTIF' ? 'text-green-600' : 'text-yellow-600';
                const marker = L.marker([item.latitude, item.longitude], { icon: icons.fab })
                    .bindPopup('<div class="text-sm"><div class="font-bold text-pink-600">Pelanggan</div><div class="text-gray-800 font-medium">' + item.nama_pelanggan + '</div><div class="text-xs text-gray-400 mt-1">' + (item.alamat || '-') + '</div><div class="text-xs ' + statusColor + ' mt-1 font-medium">' + item.status + '</div></div>');
                markerLayers.fab.addLayer(marker);
            }
        });

        // Add all layers to map
        markerLayers.pop.addTo(map);
        markerLayers.olt.addTo(map);
        markerLayers.odp.addTo(map);
        markerLayers.fab.addTo(map);
    }

    // Toggle layer visibility
    function toggleLayer(type) {
        const btn = document.getElementById('btn-' + type);
        if (map.hasLayer(markerLayers[type])) {
            map.removeLayer(markerLayers[type]);
            btn.classList.remove('active');
        } else {
            markerLayers[type].addTo(map);
            btn.classList.add('active');
        }
    }

    // Fit bounds to show all markers
    function fitBounds() {
        const allMarkers = [];
        Object.values(markerLayers).forEach(layer => {
            layer.eachLayer(marker => {
                allMarkers.push(marker.getLatLng());
            });
        });
        if (allMarkers.length > 0) {
            const bounds = L.latLngBounds(allMarkers);
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    // Initialize
    addMarkers();
    fitBounds();
</script>
@endpush
@endsection
