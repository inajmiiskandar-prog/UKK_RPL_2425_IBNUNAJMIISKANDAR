@extends('layouts.dashboard')

@section('title', 'Detail Area')
@section('page-title', 'Detail Area')
@section('page-breadcrumb', 'Master Data / Area / Detail')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('masterdata.area.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali
    </a>
    <div class="flex gap-2">
        <a href="{{ route('masterdata.area.edit', $area->id_area) }}"
           class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit
        </a>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    {{-- Info Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-6 flex items-center gap-4">
            <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/30">
                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-display text-xl font-bold text-gray-800 dark:text-white">{{ $area->nama_area }}</h2>
                <span class="inline-flex items-center rounded-full bg-purple-100 px-3 py-0.5 text-xs font-semibold text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                    {{ $area->kode_area }}
                </span>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Keterangan</p>
                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $area->keterangan ?? '-' }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Jumlah POP</p>
                    <p class="mt-1 text-lg font-bold text-gray-800 dark:text-white">{{ $area->pops->count() }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Jumlah Pelanggan</p>
                    <p class="mt-1 text-lg font-bold text-gray-800 dark:text-white">{{ $area->fabs->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Related POPs --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-display text-sm font-semibold text-gray-800 dark:text-white">POP dalam Area Ini</h3>
        @if($area->pops->count() > 0)
        <div class="space-y-3">
            @foreach($area->pops as $pop)
            <div class="flex items-center justify-between rounded-xl border border-gray-100 p-3 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                        <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $pop->nama_pop }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $pop->kode_pop }}</p>
                    </div>
                </div>
                <span class="text-xs text-gray-400">{{ $pop->olts->count() }} OLT</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-8 text-center">
            <svg class="h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada POP dalam area ini</p>
        </div>
        @endif
    </div>
</div>
@endsection
