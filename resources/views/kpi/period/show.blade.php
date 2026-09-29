@extends('layouts.dashboard')

@section('title', 'Detail Periode KPI')
@section('page-title', 'Detail Periode KPI')
@section('page-breadcrumb', 'KPI / Periode / Detail')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <a href="{{ route('kpi.period.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
    <div class="flex gap-2">
        @if($period->status === 'draft')
        <form action="{{ route('kpi.period.activate', $period->id) }}" method="POST" class="inline">@csrf
            <button type="submit" onclick="return confirm('Aktifkan periode ini dan generate assessments untuk semua karyawan?')" class="rounded-lg bg-green-100 px-4 py-2 text-sm font-medium text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400">
                Aktifkan & Generate
            </button>
        </form>
        @endif
        <a href="{{ route('kpi.period.edit', $period->id) }}" class="rounded-lg bg-blue-100 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400">Edit</a>
    </div>
</div>

{{-- Info Periode --}}
<div class="mb-6 grid gap-4 sm:grid-cols-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm text-gray-500 dark:text-gray-400">Nama</p>
        <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">{{ $period->nama }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal</p>
        <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">
            {{ $period->tanggal_mulai->format('d/m/Y') }} - {{ $period->tanggal_selesai->format('d/m/Y') }}
        </p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
        <p class="mt-1">
            @php
                $statusClass = match($period->status) {
                    'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                    'aktif' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                    'selesai' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                };
            @endphp
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-semibold capitalize {{ $statusClass }}">
                {{ $period->status }}
            </span>
        </p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Assessment</p>
        <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">{{ $period->assessments->count() }}</p>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))<div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">{{ session('error') }}</div>@endif

{{-- Daftar Assessment --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="border-b border-gray-100 px-4 py-3 dark:border-slate-700">
        <h3 class="font-semibold text-gray-800 dark:text-white">Daftar Penilaian</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-slate-700 dark:bg-slate-700/50">
                <tr>
                    <th class="px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Karyawan</th>
                    <th class="px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Atasan</th>
                    <th class="px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    <th class="px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Skor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @forelse($period->assessments as $assessment)
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700">
                    <td class="px-6 py-3 font-medium text-gray-800 dark:text-white">{{ $assessment->user->nama ?? '-' }}</td>
                    <td class="px-6 py-3 text-gray-500 dark:text-gray-400">{{ $assessment->atasan->nama ?? '-' }}</td>
                    <td class="px-6 py-3">
                        @php
                            $sClass = match($assessment->status) {
                                'pending' => 'bg-gray-100 text-gray-700',
                                'self_done' => 'bg-yellow-100 text-yellow-700',
                                'atasan_done' => 'bg-orange-100 text-orange-700',
                                'selesai' => 'bg-green-100 text-green-700',
                            };
                            $sLabel = match($assessment->status) {
                                'pending' => 'Pending',
                                'self_done' => 'Self Selesai',
                                'atasan_done' => 'Atasan Selesai',
                                'selesai' => 'Selesai',
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $sClass }}">
                            {{ $sLabel }}
                        </span>
                    </td>
                    <td class="px-6 py-3 font-semibold text-gray-800 dark:text-white">
                        {{ $assessment->skor_akhir !== null ? number_format($assessment->skor_akhir, 1) : '-' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    Belum ada assessment. Klik "Aktifkan & Generate" untuk membuat assessments otomatis.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
