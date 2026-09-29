@extends('layouts.dashboard')

@section('title', 'Penilaian Atasan')
@section('page-title', 'Penilaian Bawahan')
@section('page-breadcrumb', 'KPI / Penilaian / Penilaian Atasan')

@section('content')
<div class="mb-6">
    <a href="{{ route('kpi.assessment.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
</div>

<div class="mb-6 rounded-xl border border-orange-200 bg-orange-50 p-4 dark:border-orange-900 dark:bg-orange-900/20">
    <h3 class="font-semibold text-orange-700 dark:text-orange-300">Penilaian Atasan - {{ $assessment->user->nama }}</h3>
    <p class="text-sm text-orange-600 dark:text-orange-400">Tanggal: {{ $assessment->created_at->format('d/m/Y') }}</p>
    @if($assessment->selfScores()->count() > 0)
    <p class="mt-1 text-sm text-orange-600 dark:text-orange-400">
        Self Assessment sudah selesai. Rata-rata skor self: <strong>{{ number_format($assessment->selfScores()->avg('skor'), 1) }}</strong>
    </p>
    @endif
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

<form action="{{ route('kpi.assessment.atasan.store', $assessment->id) }}" method="POST">
    @csrf

    {{-- Soft Skills Section --}}
    @if($softSkills->count() > 0)
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-display text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <span class="rounded-full bg-blue-100 p-2 dark:bg-blue-900/30">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </span>
            Soft Skill
        </h3>
        <div class="space-y-4">
            @foreach($softSkills as $skill)
            <div class="rounded-lg border border-gray-100 p-4 dark:border-slate-700">
                <div class="mb-2">
                    <p class="font-medium text-gray-800 dark:text-white">{{ $skill->nama_indikator }}</p>
                    @if($skill->deskripsi)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $skill->deskripsi }}</p>
                    @endif
                </div>
                {{-- Tampilkan skor self assessment jika ada --}}
                @php $selfScore = $assessment->selfScores()->where('skill_id', $skill->id)->first(); @endphp
                @if($selfScore)
                <div class="mb-3 text-sm text-blue-600 dark:text-blue-400">
                    Skor Self Assessment: <strong>{{ $selfScore->skor }}</strong>
                    @if($selfScore->catatan)
                    <span class="text-gray-500">- "{{ $selfScore->catatan }}"</span>
                    @endif
                </div>
                @endif
                <div class="flex items-center gap-4">
                    <input type="range" id="range-atasan-{{ $skill->id }}" min="1" max="100" value="{{ old("scores.{$skill->id}", $atasanScores->get($skill->id)?->skor ?? 50) }}"
                           class="flex-1 h-2 rounded-lg bg-gray-200 appearance-none cursor-pointer dark:bg-slate-600"
                           oninput="document.getElementById('atasan-score-{{ $skill->id }}').value = this.value">
                    <input type="number" name="scores[soft_skill][{{ $skill->id }}]" min="1" max="100"
                           value="{{ old("scores.{$skill->id}", $atasanScores->get($skill->id)?->skor ?? 50) }}"
                           class="w-16 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1 text-center text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                           id="atasan-score-{{ $skill->id }}">
                </div>
                <div class="mt-2">
                    <input type="text" name="notes[soft_skill][{{ $skill->id }}]" value="{{ old("notes.soft_skill.{$skill->id}", $atasanScores->get($skill->id)?->catatan ?? '') }}"
                           placeholder="Catatan penilaian (opsional)"
                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Hard Skills Section --}}
    @if($hardSkills->count() > 0)
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-display text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <span class="rounded-full bg-purple-100 p-2 dark:bg-purple-900/30">
                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </span>
            Hard Skill
        </h3>
        <div class="space-y-4">
            @foreach($hardSkills as $skill)
            <div class="rounded-lg border border-gray-100 p-4 dark:border-slate-700">
                <div class="mb-2 flex items-start justify-between">
                    <div>
                        <p class="font-medium text-gray-800 dark:text-white">{{ $skill->kpi }}</p>
                        @if($skill->responsibilities)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $skill->responsibilities }}</p>
                        @endif
                        @if($skill->target)
                        <p class="mt-1 text-xs text-purple-600 dark:text-purple-400">Target: {{ $skill->target }}</p>
                        @endif
                    </div>
                    @if($skill->weight > 0)
                    <span class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                        Weight: {{ $skill->weight }}%
                    </span>
                    @endif
                </div>
                @php $selfScore = $assessment->selfScores()->where('skill_id', $skill->id)->first(); @endphp
                @if($selfScore)
                <div class="mb-3 text-sm text-blue-600 dark:text-blue-400">
                    Skor Self Assessment: <strong>{{ $selfScore->skor }}</strong>
                    @if($selfScore->catatan)
                    <span class="text-gray-500">- "{{ $selfScore->catatan }}"</span>
                    @endif
                </div>
                @endif
                <div class="flex items-center gap-4">
                    <input type="range" id="range-atasan-hard-{{ $skill->id }}" min="1" max="100" value="{{ old("scores.{$skill->id}", $atasanScores->get($skill->id)?->skor ?? 50) }}"
                           class="flex-1 h-2 rounded-lg bg-gray-200 appearance-none cursor-pointer dark:bg-slate-600"
                           oninput="document.getElementById('atasan-score-hard-{{ $skill->id }}').value = this.value">
                    <input type="number" name="scores[hard_skill][{{ $skill->id }}]" min="1" max="100"
                           value="{{ old("scores.{$skill->id}", $atasanScores->get($skill->id)?->skor ?? 50) }}"
                           class="w-16 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1 text-center text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                           id="atasan-score-hard-{{ $skill->id }}">
                </div>
                <div class="mt-2">
                    <input type="text" name="notes[hard_skill][{{ $skill->id }}]" value="{{ old("notes.hard_skill.{$skill->id}", $atasanScores->get($skill->id)?->catatan ?? '') }}"
                           placeholder="Catatan penilaian (opsional)"
                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="flex justify-end gap-3">
        <a href="{{ route('kpi.assessment.index') }}" class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600">Batal</a>
        <button type="submit" class="rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-orange-700">Simpan Penilaian</button>
    </div>
</form>
@endsection

@push('styles')
<style>
    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #ea580c;
        cursor: pointer;
    }
    input[type="range"]::-moz-range-thumb {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #ea580c;
        cursor: pointer;
        border: none;
    }
</style>
@endpush
