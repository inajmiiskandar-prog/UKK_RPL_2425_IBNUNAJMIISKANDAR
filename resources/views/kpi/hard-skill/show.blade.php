@extends('layouts.dashboard')

@section('title', 'Detail Hard Skill')
@section('page-title', 'Detail Indikator Hard Skill')
@section('page-breadcrumb', 'KPI / Hard Skill / Detail')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('kpi.hard-skill.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
</div>

<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <span class="rounded-full bg-purple-100 px-3 py-1 text-lg font-bold text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                {{ $hardSkill->kode }}
            </span>
            <h2 class="font-display text-xl font-semibold text-gray-800 dark:text-white">{{ $hardSkill->kpi ?? $hardSkill->nama_indikator }}</h2>
        </div>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Divisi</h3>
            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">{{ $hardSkill->divisi ?? '-' }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Jabatan</h3>
            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">{{ $hardSkill->jabatan ?? '-' }}</p>
        </div>
    </div>

    <div class="mt-6">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Responsibilities</h3>
        <p class="mt-1 text-gray-800 dark:text-white">{{ $hardSkill->responsibilities ?? '-' }}</p>
    </div>

    <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Target</h3>
            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">{{ $hardSkill->target ?? '100%' }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Weight (%)</h3>
            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">{{ $hardSkill->weight ? $hardSkill->weight . '%' : '-' }}</p>
        </div>
    </div>

    @if($hardSkill->deskripsi)
    <div class="mt-6">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Keterangan ( Lama)</h3>
        <p class="mt-1 text-gray-800 dark:text-white">{{ $hardSkill->deskripsi }}</p>
    </div>
    @endif

    <div class="border-t border-gray-100 mt-6 pt-4 dark:border-slate-700">
        <p class="text-xs text-gray-400 dark:text-gray-500">
            Dibuat: {{ $hardSkill->created_at->format('d/m/Y H:i') }} | Diubah: {{ $hardSkill->updated_at->format('d/m/Y H:i') }}
        </p>
    </div>
</div>
@endsection
