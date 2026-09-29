@extends('layouts.dashboard')

@section('title', 'Wizard Penilaian KPI - Step 3')
@section('page-title', 'Wizard Penilaian KPI')
@section('page-breadcrumb', 'KPI / Penilaian / Wizard')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('kpi.assessment.wizard.step2') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
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
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-600 text-sm font-bold text-white">3</div>
            <span class="font-medium text-purple-600 dark:text-purple-400">Hard Skill</span>
        </div>
        <div class="flex-1 h-1 mx-4 bg-gray-200 dark:bg-slate-700"><div class="h-full w-0 bg-purple-600"></div></div>
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-sm font-bold text-gray-500 dark:bg-slate-700 dark:text-gray-400">4</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Review</span>
        </div>
    </div>
</div>

{{-- Info Box --}}
<div class="mb-6 rounded-xl border border-purple-200 bg-purple-50 p-4 dark:border-purple-900 dark:bg-purple-900/20">
    <h3 class="font-semibold text-purple-700 dark:text-purple-300">Step 3: Review Hard Skill Atasan</h3>
    <p class="mt-1 text-sm text-purple-600 dark:text-purple-400">
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

{{-- Pesan: Divisi/Jabatan belum diisi --}}
@if(isset($missingPosition) && $missingPosition)
<div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-900 dark:bg-amber-900/20">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h4 class="font-semibold text-amber-800 dark:text-amber-300">Divisi/Jabatan Belum Diisi</h4>
            <p class="mt-1 text-sm text-amber-700 dark:text-amber-400">
                Data <strong>{{ $employeeName ?? 'karyawan' }}</strong> belum memiliki informasi Divisi dan/atau Jabatan.
                Silakan lengkapi terlebih dahulu sebelum melakukan penilaian Hard Skill.
            </p>
            <div class="mt-4">
                <a href="{{ route('users.edit', $employeeId ?? 0) }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Data Karyawan
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Skip button jika tidak ada hard skill untuk divisi/jabatan ini --}}
<div class="flex justify-end">
    <form action="{{ route('kpi.assessment.wizard.step3.store') }}" method="POST">
        @csrf
        <input type="hidden" name="skip" value="1">
        <button type="submit" class="rounded-xl bg-purple-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">
            Lewati Hard Skill
            <svg class="ml-2 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </form>
</div>
@elseif($hardSkills->isEmpty())
{{-- Pesan: Tidak ada hard skill untuk divisi/jabatan ini --}}
<div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-6 dark:border-blue-900 dark:bg-blue-900/20">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h4 class="font-semibold text-blue-800 dark:text-blue-300">Belum Ada Indikator Hard Skill</h4>
            <p class="mt-1 text-sm text-blue-700 dark:text-blue-400">
                Belum ada indikator Hard Skill yang dibuat untuk divisi dan jabatan karyawan ini.
                Hubungi administrator untuk menambahkan indikator Hard Skill yang sesuai.
            </p>
        </div>
    </div>
</div>

{{-- Skip button --}}
<div class="flex justify-end">
    <form action="{{ route('kpi.assessment.wizard.step3.store') }}" method="POST">
        @csrf
        <input type="hidden" name="skip" value="1">
        <button type="submit" class="rounded-xl bg-purple-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">
            Lewati Hard Skill
            <svg class="ml-2 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </form>
</div>
@else
{{-- Form penilaian hard skill --}}
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

<form action="{{ route('kpi.assessment.wizard.step3.store') }}" method="POST">
    @csrf

    <div class="space-y-4">
        @foreach($hardSkills as $skill)
        @php
            $scoreValue = old("scores.{$skill->id}", $wizardData['atasan_hard_skills'][$skill->id] ?? '');
            $noteValue = old("notes.{$skill->id}", $wizardData['atasan_hard_notes'][$skill->id] ?? '');
            $selfScore = $selfScores->get($skill->id);
        @endphp
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="mb-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                        {{ $skill->kode }}
                    </span>
                    @if($skill->weight > 0)
                    <span class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                        Weight: {{ $skill->weight }}%
                    </span>
                    @endif
                </div>
                <h4 class="mt-2 font-semibold text-gray-800 dark:text-white">{{ $skill->kpi }}</h4>
                @if($skill->responsibilities)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $skill->responsibilities }}</p>
                @endif
                @if($skill->target)
                <p class="mt-1 text-xs text-purple-600 dark:text-purple-400">Target: {{ $skill->target }}</p>
                @endif
            </div>

            {{-- Dropdown Penilaian --}}
            <div class="mb-3">
                <p class="mb-2 text-sm text-blue-600 dark:text-blue-400">Self-assessment karyawan: <strong>{{ $selfScore?->skor ?? '-' }}</strong></p>
                <select name="scores[{{ $skill->id }}]" id="score-hard-{{ $skill->id }}"
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
        <a href="{{ route('kpi.assessment.wizard.step2') }}" class="rounded-xl border border-gray-200 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600">
            Sebelumnya
        </a>
        <button type="submit" class="rounded-xl bg-purple-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">
            Selanjutnya
            <svg class="ml-2 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>
</form>
@endif
@endsection
