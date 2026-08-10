@extends('layouts.dashboard')

@section('title', 'Detail Port PON')
@section('page-title', 'Detail Port PON')
@section('page-breadcrumb', 'Master Data / Port PON / Detail')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('masterdata.port-pon.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
    <a href="{{ route('masterdata.port-pon.edit', $portPon->id_port) }}" class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="mb-6 flex items-center gap-4">
        <div class="rounded-full bg-cyan-100 p-3 dark:bg-cyan-900/30">
            <svg class="h-6 w-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        </div>
        <div>
            <h2 class="font-display text-xl font-bold text-gray-800 dark:text-white">Port {{ $portPon->nomor_port }}</h2>
            <span class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold @if($portPon->status == 'TERSEDIA') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 @elseif($portPon->status == 'TERPASANG') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 @else bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 @endif">
                {{ $portPon->status }}
            </span>
        </div>
    </div>
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">OLT</p><p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">{{ $portPon->olt->nama_olt ?? '-' }}</p></div>
            <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Tipe Kartu</p><p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">{{ $portPon->tipe_kartu }}</p></div>
        </div>
    </div>
</div>
@endsection
