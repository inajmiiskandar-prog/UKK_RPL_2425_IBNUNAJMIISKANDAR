@extends('layouts.dashboard')

@section('title', 'Wizard Penilaian KPI - Step 2')
@section('page-title', 'Wizard Penilaian KPI')
@section('page-breadcrumb', 'KPI / Penilaian / Wizard')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('kpi.assessment.wizard.step1') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
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
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-600 text-sm font-bold text-white">2</div>
            <span class="font-medium text-purple-600 dark:text-purple-400">Soft Skill</span>
        </div>
        <div class="flex-1 h-1 mx-4 bg-gray-200 dark:bg-slate-700"><div class="h-full w-0 bg-purple-600"></div></div>
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-sm font-bold text-gray-500 dark:bg-slate-700 dark:text-gray-400">3</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Hard Skill</span>
        </div>
        <div class="flex-1 h-1 mx-4 bg-gray-200 dark:bg-slate-700"><div class="h-full w-0 bg-gray-200 dark:bg-slate-700"></div></div>
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-sm font-bold text-gray-500 dark:bg-slate-700 dark:text-gray-400">4</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Review</span>
        </div>
    </div>
</div>

{{-- Info Box --}}
<div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
    <h3 class="font-semibold text-blue-700 dark:text-blue-300">Step 2: Review Soft Skill Atasan</h3>
    <p class="mt-1 text-sm text-blue-600 dark:text-blue-400">
        {{ $wizardData['period_nama'] ?? '-' }} | Karyawan: <strong>{{ $wizardData['user_nama'] ?? '-' }}</strong>
    </p>
</div>

@if($errors->any())
<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
    <ul class="list-inside list-disc">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@php
    // Skala penilaian: label => nilai numerik
    $penilaianScale = [
        'Sangat Baik' => 100,
        'Baik' => 80,
        'Cukup' => 60,
        'Kurang' => 40,
        'Sangat Kurang' => 20,
    ];
@endphp

<form action="{{ route('kpi.assessment.wizard.step2.store') }}" method="POST">
    @csrf

    <div class="space-y-4">
        @foreach($softSkills as $skill)
        @php
            $scoreValue = old("scores.{$skill->id}", $wizardData['atasan_soft_skills'][$skill->id] ?? '');
            $noteValue = old("notes.{$skill->id}", $wizardData['atasan_soft_notes'][$skill->id] ?? '');
            $selfScore = $selfScores->get($skill->id);
        @endphp
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="mb-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                        {{ $skill->kode }}
                    </span>
                    <h4 class="font-semibold text-gray-800 dark:text-white">{{ $skill->nama_indikator }}</h4>
                </div>
                @if($skill->deskripsi)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $skill->deskripsi }}</p>
                @endif
            </div>

            {{-- Dropdown Penilaian --}}
            <div class="mb-3">
                <p class="mb-2 text-sm text-blue-600 dark:text-blue-400">Self-assessment karyawan: <strong>{{ $selfScore?->skor ?? '-' }}</strong></p>
                <select name="scores[{{ $skill->id }}]" id="score-{{ $skill->id }}"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                        required>
                    <option value="">-- Pilih Penilaian --</option>
                    @foreach($penilaianScale as $label => $value)
                    <option value="{{ $value }}" {{ $scoreValue == $value ? 'selected' : '' }}>
                        {{ $label }} ({{ $value }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 text-[10px] font-medium text-gray-500 dark:text-gray-400">
                @foreach($penilaianScale as $label => $value)
                    <span class="{{ $scoreValue == $value ? 'text-purple-600 font-bold' : '' }}">{{ $label }} ({{ $value }})</span>
                @endforeach
            </div>

            <div class="mt-3">
                <input type="text" name="notes[{{ $skill->id }}]" value="{{ $noteValue }}"
                       placeholder="Catatan (opsional)"
                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8 flex justify-between">
        <a href="{{ route('kpi.assessment.wizard.step1') }}" class="rounded-xl border border-gray-200 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600">
            Sebelumnya
        </a>
        <button type="submit" class="rounded-xl bg-purple-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">
            Selanjutnya
            <svg class="ml-2 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>
</form>
@endsection
