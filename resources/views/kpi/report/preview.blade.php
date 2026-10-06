@extends('layouts.dashboard')

@section('title', 'Preview Laporan KPI')
@section('page-title', 'Preview Laporan KPI')
@section('page-breadcrumb', 'KPI / Laporan / Preview')

@section('content')
<div class="mb-6">
    <h1 class="font-display text-2xl font-bold text-gray-800 dark:text-white">Preview Laporan KPI</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400">Rekapitulasi penilaian KPI periode {{ $period->nama }}</p>
</div>

@if(session('success'))<div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">{{ session('error') }}</div>@endif

<div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Periode</p>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">{{ $period->nama }}</h3>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('kpi.report.index') }}" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600">
                Kembali
            </a>
            <a href="{{ route('kpi.report.exportCsv', $period->id) }}" class="rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                Download CSV
            </a>
            <a href="{{ route('kpi.report.exportExcel', $period->id) }}" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                Download Excel
            </a>
        </div>
    </div>
</div>

<div class="mb-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Karyawan</p>
        <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['total'] }}</p>
    </div>
    <div class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-900/20">
        <p class="text-sm text-green-600 dark:text-green-400">Selesai</p>
        <p class="mt-1 text-2xl font-bold text-green-700 dark:text-green-400">{{ $stats['completed'] }}</p>
    </div>
    <div class="rounded-xl border border-purple-200 bg-purple-50 p-4 dark:border-purple-900 dark:bg-purple-900/20">
        <p class="text-sm text-purple-600 dark:text-purple-400">Rata-rata Skor</p>
        <p class="mt-1 text-2xl font-bold text-purple-700 dark:text-purple-400">
            {{ $stats['avg_score'] !== null ? number_format($stats['avg_score'], 1) : '-' }}
        </p>
    </div>
</div>

@include('kpi.report._table', ['rows' => $rows, 'columns' => $columns])
@endsection
