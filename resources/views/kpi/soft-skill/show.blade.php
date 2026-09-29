@extends('layouts.dashboard')

@section('title', 'Detail Soft Skill')
@section('page-title', 'Detail Indikator Soft Skill')
@section('page-breadcrumb', 'KPI / Soft Skill / Detail')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('kpi.soft-skill.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
    <div class="flex gap-2">
        <a href="{{ route('kpi.soft-skill.edit', $softSkill->id) }}" class="rounded-lg bg-blue-100 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400">Edit</a>
    </div>
</div>

<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="space-y-4">
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Kode</h3>
            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">{{ $softSkill->kode }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi</h3>
            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">{{ $softSkill->nama_indikator }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi</h3>
            <p class="mt-1 text-gray-800 dark:text-white">{{ $softSkill->deskripsi ?? '-' }}</p>
        </div>
        <div class="border-t border-gray-100 pt-4 dark:border-slate-700">
            <p class="text-xs text-gray-400 dark:text-gray-500">Dibuat: {{ $softSkill->created_at->format('d/m/Y H:i') }} | Diubah: {{ $softSkill->updated_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</div>
@endsection
