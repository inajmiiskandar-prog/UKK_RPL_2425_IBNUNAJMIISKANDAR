@extends('layouts.dashboard')

@section('title', 'Export Data KPI')
@section('page-title', 'Export Data KPI')
@section('page-breadcrumb', 'KPI / Laporan / Export')

@section('content')
<div class="mb-6">
    <h1 class="font-display text-2xl font-bold text-gray-800 dark:text-white">Export Data KPI</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400">Download laporan rekapitulasi skor KPI dalam format CSV atau Excel</p>
</div>

@if(session('success'))<div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">{{ session('error') }}</div>@endif

{{-- Select Periode --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Pilih Periode</h3>
    <form action="{{ route('kpi.report.preview') }}" method="GET" class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[200px]">
            <select name="period_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="">-- Pilih Periode --</option>
                @foreach($periods as $p)
                <option value="{{ $p->id }}" {{ $selectedPeriod && $selectedPeriod->id == $p->id ? 'selected' : '' }}>
                    {{ $p->nama }} ({{ $p->status }})
                </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">
            Lihat Preview
        </button>
    </form>
</div>

{{-- Preview --}}
@if($selectedPeriod)
@php
    $period = $selectedPeriod;
    $assessments = \App\Models\KpiAssessment::with(['user', 'atasan'])
                               ->where('kpi_period_id', $period->id)
                               ->orderBy(
                                   \App\Models\User::query()
                                       ->select('nama')
                                       ->whereColumn('users.id_user', 'kpi_assessments.user_id')
                                       ->limit(1),
                                   'asc'
                               )
                               ->get();

    $stats = [
        'total' => $assessments->count(),
        'completed' => $assessments->where('status', 'selesai')->count(),
        'avg_score' => $assessments->whereNotNull('skor_akhir')->avg('skor_akhir'),
    ];
@endphp

{{-- Stats --}}
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

{{-- Export Buttons --}}
<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ route('kpi.report.exportCsv', $period->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Download CSV
    </a>
    <a href="{{ route('kpi.report.exportExcel', $period->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Download Excel
    </a>
</div>

{{-- Preview Table --}}
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-slate-700 dark:bg-slate-700/50">
                <tr>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">#</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">NIK</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Nama</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Divisi</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Atasan</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Skor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @forelse($assessments as $a)
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700">
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $a->user->nik ?? '-' }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">{{ $a->user->nama }}</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $a->user->divisi ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $a->atasan->nama ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @php
                            $sClass = match($a->status) {
                                'selesai' => 'bg-green-100 text-green-700',
                                'atasan_done' => 'bg-orange-100 text-orange-700',
                                'self_done' => 'bg-yellow-100 text-yellow-700',
                                'menunggu_review' => 'bg-yellow-100 text-yellow-700',
                                'sudah_dicek' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $sClass }}">{{ $a->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-purple-600 dark:text-purple-400">
                        {{ $a->skor_akhir !== null ? number_format($a->skor_akhir, 1) : '-' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
