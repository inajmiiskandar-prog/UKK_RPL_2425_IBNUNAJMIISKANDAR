@extends('layouts.dashboard')

@section('title', 'Detail ONT')
@section('page-title', 'Detail ONT')
@section('page-breadcrumb', 'Master Data / ONT / Detail')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('masterdata.ont.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
    <a href="{{ route('masterdata.ont.edit', $ont->id_ont) }}" class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="mb-6 flex items-center gap-4">
        <div class="rounded-full bg-cyan-100 p-3 dark:bg-cyan-900/30">
            <svg class="h-6 w-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <h2 class="font-display text-xl font-bold text-gray-800 dark:text-white">{{ $ont->serial_number }}</h2>
            <span class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold @if($ont->status == 'TERSEDIA') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 @elseif($ont->status == 'TERPASANG') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 @else bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 @endif">
                {{ $ont->status }}
            </span>
        </div>
    </div>
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Pelanggan</p><p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">{{ $ont->pelanggan }}</p></div>
            <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">POP</p><p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">{{ $ont->pop->nama_pop ?? '-' }}</p></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Area</p><p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">{{ $ont->pop->area->nama_area ?? '-' }}</p></div>
            <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">ODP</p><p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">{{ $ont->odp->nama_odp ?? '-' }}</p></div>
        </div>
    </div>
</div>
@endsection
