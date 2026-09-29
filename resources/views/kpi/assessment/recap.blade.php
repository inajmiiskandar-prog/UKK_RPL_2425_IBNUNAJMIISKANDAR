@extends('layouts.dashboard')

@section('title', 'Rekap KPI')
@section('page-title', 'Rekapitulasi KPI')
@section('page-breadcrumb', 'KPI / Rekap')

@section('content')
{{-- Filter Periode --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[200px]">
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Periode</label>
            <select name="period_id" onchange="this.form.submit()" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="">Semua Periode</option>
                @foreach($periods as $p)
                <option value="{{ $p->id }}" {{ $selectedPeriod && $selectedPeriod->id == $p->id ? 'selected' : '' }}>
                    {{ $p->nama }} ({{ $p->status }})
                </option>
                @endforeach
            </select>
        </div>
    </form>
</div>

{{-- Summary Stats --}}
<div class="mb-6 grid gap-4 sm:grid-cols-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Penilaian</p>
        <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ $assessments->count() }}</p>
    </div>
    <div class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-900/20">
        <p class="text-sm text-green-600 dark:text-green-400">Selesai</p>
        <p class="mt-1 text-2xl font-bold text-green-700 dark:text-green-400">{{ $assessments->where('status', 'selesai')->count() }}</p>
    </div>
    <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-900 dark:bg-yellow-900/20">
        <p class="text-sm text-yellow-600 dark:text-yellow-400">Pending</p>
        <p class="mt-1 text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $assessments->whereNotIn('status', ['selesai'])->count() }}</p>
    </div>
    <div class="rounded-xl border border-purple-200 bg-purple-50 p-4 dark:border-purple-900 dark:bg-purple-900/20">
        <p class="text-sm text-purple-600 dark:text-purple-400">Rata-rata Skor</p>
        <p class="mt-1 text-2xl font-bold text-purple-700 dark:text-purple-400">
            {{ $assessments->whereNotNull('skor_akhir')->count() > 0 ? number_format($assessments->whereNotNull('skor_akhir')->avg('skor_akhir'), 1) : '-' }}
        </p>
    </div>
</div>

{{-- Table --}}
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-slate-700 dark:bg-slate-700/50">
                <tr>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">#</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Karyawan</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Atasan</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Tanggal</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Skor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @forelse($assessments as $a)
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700">
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800 dark:text-white">{{ $a->user->nama ?? '-' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $a->user->divisi ?? '-' }} / {{ $a->user->jabatan ?? '-' }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $a->atasan->nama ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $a->created_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        @php
                            $sClass = match($a->status) {
                                'pending' => 'bg-gray-100 text-gray-700',
                                'self_done' => 'bg-yellow-100 text-yellow-700',
                                'menunggu_review' => 'bg-yellow-100 text-yellow-700',
                                'sudah_dicek' => 'bg-green-100 text-green-700',
                                'atasan_done' => 'bg-orange-100 text-orange-700',
                                'selesai' => 'bg-green-100 text-green-700',
                            };
                            $sLabel = match($a->status) {
                                'pending' => 'Pending',
                                'self_done' => 'Self Selesai',
                                'menunggu_review' => 'Menunggu review atasan',
                                'sudah_dicek' => 'Sudah selesai dinilai',
                                'atasan_done' => 'Atasan Selesai',
                                'selesai' => 'Selesai',
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $sClass }}">
                            {{ $sLabel }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($a->skor_akhir !== null)
                        <span class="font-semibold text-purple-600 dark:text-purple-400">{{ number_format($a->skor_akhir, 1) }}</span>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center"><p class="text-gray-500 dark:text-gray-400">Belum ada data penilaian</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
