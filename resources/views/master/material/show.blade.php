@extends('layouts.dashboard')

@section('title', 'Detail Material')
@section('page-title', 'Detail Material')
@section('page-breadcrumb', 'Master Data / Material / Detail')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('masterdata.material.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
    <a href="{{ route('masterdata.material.edit', $material->id_material) }}" class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="mb-6 flex items-center gap-4">
        <div class="rounded-full bg-teal-100 p-3 dark:bg-teal-900/30">
            <svg class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
        <div>
            <h2 class="font-display text-xl font-bold text-gray-800 dark:text-white">{{ $material->nama_material }}</h2>
            <span class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold @if($material->kondisi == 'BAIK') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 @else bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 @endif">
                {{ $material->kondisi }}
            </span>
        </div>
    </div>
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Kode</p><p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">{{ $material->kode_material }}</p></div>
            <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Harga</p><p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">Rp {{ number_format($material->harga, 0, ',', '.') }}</p></div>
        </div>
        <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-100 dark:border-slate-700">
            <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Stok</p><p class="mt-1 text-lg font-bold {{ $material->stok <= $material->minimal_stok ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-white' }}">{{ $material->stok }} {{ $material->satuan }}</p></div>
            <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Minimal</p><p class="mt-1 text-lg font-bold text-gray-800 dark:text-white">{{ $material->minimal_stok }} {{ $material->satuan }}</p></div>
            <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Total Nilai</p><p class="mt-1 text-lg font-bold text-purple-600 dark:text-purple-400">Rp {{ number_format($material->stok * $material->harga, 0, ',', '.') }}</p></div>
        </div>
        @if($material->keterangan)
        <div class="pt-4 border-t border-gray-100 dark:border-slate-700"><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Keterangan</p><p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $material->keterangan }}</p></div>
        @endif
    </div>
</div>
@endsection
