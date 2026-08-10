@extends('layouts.dashboard')

@section('title', 'Detail Pelanggan')
@section('page-title', 'Detail Pelanggan')
@section('page-breadcrumb', 'Jaringan / Pelanggan / Detail')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('jaringan.fab.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
    <a href="{{ route('jaringan.fab.edit', $fab->id_fab) }}" class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-6 flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-pink-500 text-white text-xl font-bold">
                {{ substr($fab->nama_pelanggan, 0, 1) }}
            </div>
            <div>
                <h2 class="font-display text-xl font-bold text-gray-800 dark:text-white">{{ $fab->nama_pelanggan }}</h2>
                <span class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold @if($fab->status == 'AKTIF') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 @else bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 @endif">
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
            <div class="pt-4 border-t border-gray-100 dark:border-slate-700"><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Alamat</p><p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $fab->alamat }}</p></div>
        </div>
    </div>
</div>
@endsection
