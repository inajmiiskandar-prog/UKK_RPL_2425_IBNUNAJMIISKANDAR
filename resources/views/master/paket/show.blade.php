@extends('layouts.dashboard')

@section('title', 'Detail Paket')
@section('page-title', 'Detail Paket')
@section('page-breadcrumb', 'Master Data / Paket / Detail')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('masterdata.paket.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
    <a href="{{ route('masterdata.paket.edit', $paket->id_paket) }}" class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-6 flex items-center gap-4">
            <div class="rounded-full bg-indigo-100 p-3 dark:bg-indigo-900/30">
                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <h2 class="font-display text-xl font-bold text-gray-800 dark:text-white">{{ $paket->nama_paket }}</h2>
                <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">{{ $paket->kode_paket }}</span>
            </div>
        </div>
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Kecepatan</p><p class="mt-1 text-lg font-bold text-gray-800 dark:text-white">{{ $paket->kecepatan }}</p></div>
                <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Harga/Bulan</p><p class="mt-1 text-lg font-bold text-green-600 dark:text-green-400">Rp {{ number_format($paket->harga, 0, ',', '.') }}</p></div>
            </div>
            @if($paket->keterangan)
            <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Keterangan</p><p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $paket->keterangan }}</p></div>
            @endif
            <div class="pt-4 border-t border-gray-100 dark:border-slate-700">
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Jumlah Pelanggan</p>
                <p class="mt-1 text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $paket->fabs->count() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
