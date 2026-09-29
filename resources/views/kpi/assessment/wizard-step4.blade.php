@extends('layouts.dashboard')

@section('title', 'Wizard Penilaian KPI - Step 4')
@section('page-title', 'Wizard Penilaian KPI')
@section('page-breadcrumb', 'KPI / Penilaian / Wizard')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('kpi.assessment.wizard.step3') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
</div>

{{-- Progress Indicator --}}
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-sm font-bold text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="text-sm text-green-600 dark:text-green-400">Selesai</span>
        </div>
        <div class="flex-1 h-1 mx-4 bg-green-600"></div>
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-sm font-bold text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="text-sm text-green-600 dark:text-green-400">Selesai</span>
        </div>
        <div class="flex-1 h-1 mx-4 bg-green-600"></div>
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-sm font-bold text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="text-sm text-green-600 dark:text-green-400">Selesai</span>
        </div>
        <div class="flex-1 h-1 mx-4 bg-green-600"></div>
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-600 text-sm font-bold text-white">4</div>
            <span class="font-medium text-purple-600 dark:text-purple-400">Review</span>
        </div>
    </div>
</div>

{{-- Info Box --}}
<div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-900/20">
    <h3 class="font-semibold text-green-700 dark:text-green-300">Step 4: Review & Submit</h3>
    <p class="mt-1 text-sm text-green-600 dark:text-green-400">
        Periksa kembali penilaian atasan sebelum disimpan ke database
    </p>
</div>

@if(session('error'))<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">{{ session('error') }}</div>@endif

{{-- Summary Info --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Periode</p>
            <p class="font-semibold text-gray-800 dark:text-white">{{ $wizardData['period_nama'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Karyawan</p>
            <p class="font-semibold text-gray-800 dark:text-white">{{ $wizardData['user_nama'] ?? '-' }}</p>
        </div>
    </div>
</div>

{{-- Soft Skills Summary --}}
@php
$softScores = $wizardData['atasan_soft_skills'] ?? [];
$softNotes = $wizardData['atasan_soft_notes'] ?? [];
$softAvg = count($softScores) > 0 ? array_sum($softScores) / count($softScores) : 0;
@endphp

<div class="mb-6 overflow-hidden rounded-xl border border-blue-200 bg-white dark:border-slate-700 dark:bg-slate-800">
    <div class="border-b border-gray-100 bg-blue-50 px-4 py-3 dark:border-slate-700 dark:bg-blue-900/20">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-blue-700 dark:text-blue-300">Soft Skill ({{ count($softScores) }} indikator)</h3>
            <span class="rounded-full bg-blue-200 px-3 py-1 text-sm font-bold text-blue-700 dark:bg-blue-800 dark:text-blue-200">
                Simple Avg: {{ number_format($softAvg, 1) }}
            </span>
        </div>
        <p class="mt-1 text-xs text-blue-600 dark:text-blue-400">* Soft skill dihitung dengan rata-rata biasa (simple average)</p>
    </div>
    <div class="divide-y divide-gray-50 dark:divide-slate-700">
        @forelse($softSkills as $skill)
        @php $score = $softScores[$skill->id] ?? null; @endphp
        @if($score)
        <div class="flex items-center justify-between px-4 py-2">
            <div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $skill->kode }}</span>
                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ $skill->nama_indikator }}</span>
                @if(!empty($softNotes[$skill->id]))
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">"{{ $softNotes[$skill->id] }}"</p>
                @endif
            </div>
            <span class="font-bold text-blue-600 dark:text-blue-400">{{ $score }}</span>
        </div>
        @endif
        @empty
        <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Tidak ada penilaian soft skill</div>
        @endforelse
    </div>
</div>

{{-- Hard Skills Summary --}}
@php
$hardScores = $wizardData['atasan_hard_skills'] ?? [];
$hardNotes = $wizardData['atasan_hard_notes'] ?? [];

// Hitung weighted average untuk preview
$weightedSum = 0;
$totalWeight = 0;
$hasValidWeight = false;

foreach ($hardScores as $skillId => $score) {
    $skill = $hardSkills->firstWhere('id', $skillId);
    if ($skill && $skill->weight > 0) {
        $weightedSum += $score * $skill->weight;
        $totalWeight += $skill->weight;
        $hasValidWeight = true;
    }
}

if ($hasValidWeight && $totalWeight > 0) {
    $hardAvg = $weightedSum / $totalWeight;
    $avgLabel = 'Weighted Avg';
} else {
    $hardAvg = count($hardScores) > 0 ? array_sum($hardScores) / count($hardScores) : 0;
    $avgLabel = count($hardScores) > 0 ? 'Simple Avg*' : '0';
}
@endphp

<div class="mb-6 overflow-hidden rounded-xl border border-purple-200 bg-white dark:border-slate-700 dark:bg-slate-800">
    <div class="border-b border-gray-100 bg-purple-50 px-4 py-3 dark:border-slate-700 dark:bg-purple-900/20">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-purple-700 dark:text-purple-300">Hard Skill ({{ count($hardScores) }} indikator)</h3>
            <span class="rounded-full bg-purple-200 px-3 py-1 text-sm font-bold text-purple-700 dark:bg-purple-800 dark:text-purple-200">
                {{ $avgLabel }}: {{ number_format($hardAvg, 1) }}
            </span>
        </div>
        @if($hasValidWeight)
        <p class="mt-1 text-xs text-purple-600 dark:text-purple-400">* Hard skill dihitung dengan weighted average (berdasarkan weight %)</p>
        @elseif(count($hardScores) > 0)
        <p class="mt-1 text-xs text-purple-600 dark:text-purple-400">* Item tidak memiliki weight, dihitung dengan simple average</p>
        @else
        <p class="mt-1 text-xs text-purple-600 dark:text-purple-400">Tidak ada penilaian hard skill</p>
        @endif
    </div>
    <div class="divide-y divide-gray-50 dark:divide-slate-700">
        @forelse($hardSkills as $skill)
        @php $score = $hardScores[$skill->id] ?? null; @endphp
        @if($score)
        <div class="flex items-center justify-between px-4 py-2">
            <div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $skill->kode }}</span>
                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ $skill->kpi }}</span>
                @if($skill->weight > 0)
                <span class="ml-2 rounded bg-purple-100 px-1.5 py-0.5 text-xs text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                    {{ $skill->weight }}%
                </span>
                @endif
                @if(!empty($hardNotes[$skill->id]))
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">"{{ $hardNotes[$skill->id] }}"</p>
                @endif
            </div>
            <span class="font-bold text-purple-600 dark:text-purple-400">{{ $score }}</span>
        </div>
        @endif
        @empty
        <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Tidak ada indikator hard skill untuk divisi/jabatan ini</div>
        @endforelse
    </div>
</div>

{{-- Overall Score Preview --}}
@php
$overallScore = 0;
$scoreCount = 0;

if ($softAvg > 0) {
    $overallScore += $softAvg;
    $scoreCount++;
}
if ($hardAvg > 0) {
    $overallScore += $hardAvg;
    $scoreCount++;
}
$overallAvg = $scoreCount > 0 ? $overallScore / $scoreCount : 0;
@endphp

<div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-900/20">
    <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-green-700 dark:text-green-300">Preview Skor Akhir (Avg Soft + Hard)</span>
        <span class="text-2xl font-bold text-green-700 dark:text-green-300">{{ number_format($overallAvg, 2) }}</span>
    </div>
    <p class="mt-1 text-xs text-green-600 dark:text-green-400">
        * Skor akhir dihitung setelah skor atasan disimpan.
    </p>
</div>

{{-- Submit Form --}}
<form action="{{ route('kpi.assessment.wizard.submit') }}" method="POST" id="submitForm">
    @csrf
    <div class="flex justify-between">
        <a href="{{ route('kpi.assessment.wizard.step3') }}" class="rounded-xl border border-gray-200 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600">
            Sebelumnya
        </a>
        <button type="submit" onclick="return confirm('Yakin ingin menyimpan penilaian ini? Data tidak dapat diubah setelah disimpan.')"
                class="rounded-xl bg-green-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
            <svg class="mr-2 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan Review Atasan
        </button>
    </div>
</form>
@endsection
