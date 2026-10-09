@extends('layouts.dashboard')

@section('title', 'Detail Penilaian KPI')
@section('page-title', 'Detail Penilaian KPI')
@section('page-breadcrumb', 'KPI / Penilaian / Detail')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('kpi.assessment.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
    @if($assessment->status === 'sudah_dicek')
    <button type="button" onclick="printAssessment()" class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">Print</button>
    @else
    <span class="text-sm text-gray-500">Menunggu review atasan</span>
    @endif
</div>

{{-- Info Assessment --}}
<div class="mb-6 grid gap-4 sm:grid-cols-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm text-gray-500 dark:text-gray-400">Karyawan</p>
        <p class="mt-1 font-semibold text-gray-800 dark:text-white">{{ $assessment->user->nama }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm text-gray-500 dark:text-gray-400">Periode</p>
        <p class="mt-1 font-semibold text-gray-800 dark:text-white">{{ $assessment->period->nama }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
        <p class="mt-1">
            @php
                $sClass = match($assessment->status) {
                    'pending' => 'bg-gray-100 text-gray-700',
                    'self_done' => 'bg-yellow-100 text-yellow-700',
                    'atasan_done' => 'bg-orange-100 text-orange-700',
                    'selesai' => 'bg-green-100 text-green-700',
                    'menunggu_review' => 'bg-yellow-100 text-yellow-700',
                    'sudah_dicek' => 'bg-green-100 text-green-700',
                    default => 'bg-gray-100 text-gray-700',
                };
                $sLabel = match($assessment->status) {
                    'pending' => 'Pending',
                    'self_done' => 'Self Selesai',
                    'atasan_done' => 'Atasan Selesai',
                    'selesai' => 'Selesai',
                    'menunggu_review' => 'Menunggu review atasan',
                    'sudah_dicek' => 'Sudah selesai dinilai',
                    default => $assessment->status,
                };
            @endphp
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium {{ $sClass }}">
                {{ $sLabel }}
            </span>
        </p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm text-gray-500 dark:text-gray-400">Skor Akhir</p>
        <p class="mt-1 text-2xl font-bold text-purple-600 dark:text-purple-400">
            {{ $assessment->skor_akhir !== null ? number_format($assessment->skor_akhir, 1) : '-' }}
        </p>
    </div>
</div>

{{-- Skor Self Assessment --}}
<div class="mb-6 rounded-xl border border-blue-200 bg-white p-6 shadow-sm dark:border-blue-900 dark:bg-slate-800">
    <h3 class="mb-4 font-display text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
        <span class="rounded-full bg-blue-100 p-2 dark:bg-blue-900/30">
            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </span>
        Self Assessment
        <span class="ml-auto text-sm font-normal text-gray-500">
            Rata-rata: <strong>{{ $selfScores->count() > 0 ? number_format($selfScores->avg('skor'), 1) : '-' }}</strong>
        </span>
    </h3>
    @if($selfScores->count() > 0)
    <div class="grid gap-3 sm:grid-cols-2">
        @foreach($selfScores as $score)
        <div class="flex items-center justify-between rounded-lg border border-blue-100 bg-blue-50/50 p-3 dark:border-blue-900 dark:bg-blue-900/20">
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $score->skill_type === 'soft_skill' ? $score->skill->nama_indikator : ($score->skill->kpi ?? $score->skill->nama_indikator ?? 'Unknown') }}</span>
            <span class="font-semibold text-blue-700 dark:text-blue-400">{{ $score->skor }}</span>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data.</p>
    @endif
</div>

{{-- Perbandingan Self vs Atasan per Indikator --}}
@if($selfScores->count() > 0 || $atasanScores->count() > 0)
@php
// Closure helper to get skill name based on type (avoids function-redeclaration issue in @php blocks)
$getSkillNama = function ($score) {
    if ($score->skill_type === 'soft_skill') {
        return $score->skill->nama_indikator ?? 'Unknown';
    }
    return $score->skill->kpi ?? $score->skill->nama_indikator ?? 'Unknown';
};

// Merge scores by skill for comparison
$comparisonData = [];
foreach ($selfScores as $score) {
    $key = $score->skill_type . ':' . $score->skill_id;
    $comparisonData[$key] = [
        'nama' => $getSkillNama($score),
        'tipe' => $score->skill_type === 'soft_skill' ? 'Soft Skill' : 'Hard Skill',
        'self' => $score->skor,
        'atasan' => null,
        'selisih' => null,
    ];
}
foreach ($atasanScores as $score) {
    $key = $score->skill_type . ':' . $score->skill_id;
    if (isset($comparisonData[$key])) {
        $comparisonData[$key]['atasan'] = $score->skor;
        $comparisonData[$key]['selisih'] = $score->skor - $comparisonData[$key]['self'];
    } else {
        $comparisonData[$key] = [
            'nama' => $getSkillNama($score),
            'tipe' => $score->skill_type === 'soft_skill' ? 'Soft Skill' : 'Hard Skill',
            'self' => null,
            'atasan' => $score->skor,
            'selisih' => null,
        ];
    }
}
@endphp

<div class="mb-6 rounded-xl border border-purple-200 bg-white p-6 shadow-sm dark:border-purple-900 dark:bg-slate-800">
    <h3 class="mb-4 font-display text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
        <span class="rounded-full bg-purple-100 p-2 dark:bg-purple-900/30">
            <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </span>
        Perbandingan Self vs Atasan
        <span class="ml-auto text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 dark:bg-slate-600 dark:text-gray-300">Selisih >20 poin di-highlight</span>
    </h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 dark:border-slate-700">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Indikator</th>
                    <th class="px-3 py-2 text-center font-semibold text-gray-600 dark:text-gray-300">Tipe</th>
                    <th class="px-3 py-2 text-center font-semibold text-blue-600 dark:text-blue-400">Skor Self</th>
                    <th class="px-3 py-2 text-center font-semibold text-orange-600 dark:text-orange-400">Skor Atasan</th>
                    <th class="px-3 py-2 text-center font-semibold text-gray-600 dark:text-gray-300">Selisih</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @foreach($comparisonData as $item)
                @php
                    $selisih = $item['selisih'];
                    $absSelisih = $selisih !== null ? abs($selisih) : 0;
                    $isSignificant = $absSelisih > 20;
                    $rowClass = $isSignificant ? 'bg-red-50 dark:bg-red-900/20' : '';
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="px-3 py-2 text-gray-800 dark:text-white">{{ $item['nama'] }}</td>
                    <td class="px-3 py-2 text-center text-gray-500 dark:text-gray-400">{{ $item['tipe'] }}</td>
                    <td class="px-3 py-2 text-center font-medium text-blue-600 dark:text-blue-400">
                        {{ $item['self'] !== null ? $item['self'] : '-' }}
                    </td>
                    <td class="px-3 py-2 text-center font-medium text-orange-600 dark:text-orange-400">
                        {{ $item['atasan'] !== null ? $item['atasan'] : '-' }}
                    </td>
                    <td class="px-3 py-2 text-center font-semibold">
                        @if($selisih !== null)
                            @php
                                $selisihClass = match(true) {
                                    $selisih > 20 => 'text-red-600 dark:text-red-400',
                                    $selisih < -20 => 'text-blue-600 dark:text-blue-400',
                                    default => 'text-gray-600 dark:text-gray-400',
                                };
                            @endphp
                            <span class="{{ $selisihClass }}">
                                @if($selisih > 0)+@endif{{ $selisih }}
                                @if($isSignificant)
                                <span class="ml-1 text-xs">(signifikan)</span>
                                @endif
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Skor Atasan --}}
<div class="rounded-xl border border-orange-200 bg-white p-6 shadow-sm dark:border-orange-900 dark:bg-slate-800">
    <h3 class="mb-4 font-display text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
        <span class="rounded-full bg-orange-100 p-2 dark:bg-orange-900/30">
            <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </span>
        Penilaian Atasan
        <span class="ml-auto text-sm font-normal text-gray-500">
            Rata-rata: <strong>{{ $atasanScores->count() > 0 ? number_format($atasanScores->avg('skor'), 1) : '-' }}</strong>
        </span>
    </h3>
    @if($atasanScores->count() > 0)
    <div class="grid gap-3 sm:grid-cols-2">
        @foreach($atasanScores as $score)
        <div class="flex items-center justify-between rounded-lg border border-orange-100 bg-orange-50/50 p-3 dark:border-orange-900 dark:bg-orange-900/20">
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $score->skill_type === 'soft_skill' ? $score->skill->nama_indikator : ($score->skill->kpi ?? $score->skill->nama_indikator ?? 'Unknown') }}</span>
            <span class="font-semibold text-orange-700 dark:text-orange-400">{{ $score->skor }}</span>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data.</p>
    @endif
</div>
@endsection

@if($assessment->status === 'sudah_dicek')
@push('scripts')
<script>
function printAssessment() {
    window.print();
}
</script>
@endpush
@endif
