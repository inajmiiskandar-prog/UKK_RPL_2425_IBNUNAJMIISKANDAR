@extends('layouts.dashboard')

@section('title', 'Self Assessment')
@section('page-title', 'Self Assessment')
@section('page-breadcrumb', 'KPI / Penilaian / Self Assessment')

@section('content')
<div class="mb-6">
    <a href="{{ route('kpi.assessment.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
</div>

<div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
    <h3 class="font-semibold text-blue-700 dark:text-blue-300">Self Assessment - {{ now()->format('F Y') }}</h3>
    <p class="text-sm text-blue-600 dark:text-blue-400">Nilai diri sendiri untuk setiap indikator di bawah ini (skala 1-100)</p>
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

@if(in_array($assessment->status, ['sudah_dicek', 'selesai'], true))
<div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-900/20 dark:text-amber-300">
    Penilaian ini sudah dicek atasan dan tidak dapat diubah.
</div>
@else
@php
    $skillStepCount = (int) $softSkills->isNotEmpty() + (int) $hardSkills->isNotEmpty();
    $firstSkillError = collect($errors->keys())->first(fn ($key) => str_starts_with($key, 'scores.') || str_starts_with($key, 'notes.'));
    $startOnHardSkill = $softSkills->isEmpty() || (
        $hardSkills->isNotEmpty()
        && is_string($firstSkillError)
        && (str_starts_with($firstSkillError, 'scores.hard_skill.') || str_starts_with($firstSkillError, 'notes.hard_skill.'))
    );
@endphp
<form action="{{ route('kpi.assessment.self.store', $assessment->id) }}" method="POST">
    @csrf

    @if($skillStepCount > 0)
    <div class="mb-4 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200"
         role="status" aria-live="polite">
        Langkah <span id="assessment-current-step">{{ $startOnHardSkill && $skillStepCount === 2 ? 2 : 1 }}</span> dari {{ $skillStepCount }}
    </div>
    @endif

    {{-- Soft Skills Section --}}
    @if($softSkills->count() > 0)
        <div id="soft-skill-step" data-step-panel="soft" @if($startOnHardSkill && $skillStepCount === 2) hidden @endif
            class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-6">
        <h3 class="mb-4 font-display text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <span class="rounded-full bg-blue-100 p-2 dark:bg-blue-900/30">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </span>
            Soft Skill
        </h3>
        <div class="space-y-4">
            @foreach($softSkills as $skill)
            <div class="rounded-lg border border-gray-100 p-4 dark:border-slate-700">
                <div class="mb-2 flex items-start justify-between">
                    <div>
                        <p class="font-medium text-gray-800 dark:text-white">{{ $skill->nama_indikator }}</p>
                        @if($skill->deskripsi)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $skill->deskripsi }}</p>
                        @endif
                    </div>
                </div>
                  <div class="flex items-center gap-3 sm:gap-4">
                      <div class="relative min-w-0 flex-1 pt-7" data-score-control>
                                             <output data-score-bubble class="pointer-events-none absolute top-0 left-0 z-10 min-w-9 -translate-x-1/2 whitespace-nowrap rounded-md bg-purple-700 px-2 py-1 text-center text-xs font-semibold text-white dark:bg-purple-500">{{ old("scores.soft_skill.{$skill->id}", $selfScores->get('soft_skill:'.$skill->id)?->skor ?? 50) }}</output>
                                             <input type="range" id="range-soft-{{ $skill->id }}" min="1" max="100" value="{{ old("scores.soft_skill.{$skill->id}", $selfScores->get('soft_skill:'.$skill->id)?->skor ?? 50) }}"
                           class="block h-8 w-full cursor-pointer appearance-none bg-transparent"
                                                     data-score-range data-number-input="score-val-{{ $skill->id }}"
                                                     aria-label="{{ $skill->nama_indikator }}" aria-valuenow="{{ old("scores.soft_skill.{$skill->id}", $selfScores->get('soft_skill:'.$skill->id)?->skor ?? 50) }}">
                      </div>
                                        <input type="hidden" name="scores[soft_skill][{{ $skill->id }}]"
                                                    value="{{ old("scores.soft_skill.{$skill->id}", $selfScores->get('soft_skill:'.$skill->id)?->skor ?? 50) }}"
                           id="score-val-{{ $skill->id }}">
                </div>
                <div class="mt-2">
                    <input type="text" name="notes[soft_skill][{{ $skill->id }}]" value="{{ old("notes.soft_skill.{$skill->id}", $selfScores->get('soft_skill:'.$skill->id)?->catatan ?? '') }}"
                           placeholder="Catatan (opsional)"
                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Hard Skills Section --}}
    @if($hardSkills->count() > 0)
        <div id="hard-skill-step" data-step-panel="hard" @if(!$startOnHardSkill && $skillStepCount === 2) hidden @endif
            class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-6">
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
                  <div class="flex items-center gap-3 sm:gap-4">
                      <div class="relative min-w-0 flex-1 pt-7" data-score-control>
                                             <output data-score-bubble class="pointer-events-none absolute top-0 left-0 z-10 min-w-9 -translate-x-1/2 whitespace-nowrap rounded-md bg-purple-700 px-2 py-1 text-center text-xs font-semibold text-white dark:bg-purple-500">{{ old("scores.hard_skill.{$skill->id}", $selfScores->get('hard_skill:'.$skill->id)?->skor ?? 50) }}</output>
                       <input type="range" id="range-hard-{{ $skill->id }}" min="1" max="100" value="{{ old("scores.hard_skill.{$skill->id}", $selfScores->get('hard_skill:'.$skill->id)?->skor ?? 50) }}"
                           class="block h-8 w-full cursor-pointer appearance-none bg-transparent"
                                                     data-score-range data-number-input="score-val-hard-{{ $skill->id }}"
                                                     aria-label="{{ $skill->kpi }}" aria-valuenow="{{ old("scores.hard_skill.{$skill->id}", $selfScores->get('hard_skill:'.$skill->id)?->skor ?? 50) }}">
                      </div>
                                        <input type="hidden" name="scores[hard_skill][{{ $skill->id }}]"
                                                    value="{{ old("scores.hard_skill.{$skill->id}", $selfScores->get('hard_skill:'.$skill->id)?->skor ?? 50) }}"
                           id="score-val-hard-{{ $skill->id }}">
                </div>
                <div class="mt-2">
                    <input type="text" name="notes[hard_skill][{{ $skill->id }}]" value="{{ old("notes.hard_skill.{$skill->id}", $selfScores->get('hard_skill:'.$skill->id)?->catatan ?? '') }}"
                           placeholder="Catatan (opsional)"
                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($softSkills->isEmpty() && $hardSkills->isEmpty())
    <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-6 text-center dark:border-yellow-800 dark:bg-yellow-900/20">
        <p class="text-yellow-700 dark:text-yellow-400">Belum ada indikator skill yang dibuat. Hubungi admin untuk menambahkan indikator.</p>
    </div>
    @elseif($hardSkills->isEmpty())
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-900/20">
        <p class="text-sm text-amber-700 dark:text-amber-400">Belum ada indikator Hard Skill untuk profil Anda. Lengkapi divisi/jabatan untuk menilai Hard Skill di periode berikutnya. Self Assessment Soft Skill tetap dapat disimpan.</p>
    </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('kpi.assessment.index') }}" class="inline-flex min-h-11 items-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600">Batal</a>
        <div class="flex flex-wrap justify-end gap-3">
            @if($softSkills->isNotEmpty() && $hardSkills->isNotEmpty())
            <button type="button" id="assessment-back" @if(!$startOnHardSkill) hidden @endif
                    class="inline-flex min-h-11 items-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600">Kembali</button>
            <button type="button" id="assessment-next" @if($startOnHardSkill) hidden @endif
                    class="inline-flex min-h-11 items-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Selanjutnya</button>
            @endif
            <button type="submit" id="assessment-submit" @if($softSkills->isNotEmpty() && $hardSkills->isNotEmpty() && !$startOnHardSkill) hidden @endif
                    class="inline-flex min-h-11 items-center rounded-xl bg-purple-600 px-5 py-3 text-sm font-semibold text-white hover:bg-purple-700">Simpan Self Assessment</button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sliders = document.querySelectorAll('[data-score-range]');

    function updateBubble(slider) {
        const control = slider.closest('[data-score-control]');
        const bubble = control.querySelector('[data-score-bubble]');
        const min = Number(slider.min);
        const max = Number(slider.max);
        const value = Number(slider.value);
        const thumbRadius = 10;
        const usableWidth = Math.max(0, slider.clientWidth - thumbRadius * 2);
        const ratio = (value - min) / (max - min);

        bubble.textContent = slider.value;
        bubble.style.left = `${thumbRadius + usableWidth * ratio}px`;
        slider.setAttribute('aria-valuenow', slider.value);
    }

    sliders.forEach(function(slider) {
        const numberInput = document.getElementById(slider.dataset.numberInput);
        numberInput.value = slider.value;

        slider.addEventListener('input', function() {
            numberInput.value = slider.value;
            updateBubble(slider);
        });

        numberInput.addEventListener('input', function() {
            if (numberInput.value === '') return;
            slider.value = numberInput.value;
            numberInput.value = slider.value;
            updateBubble(slider);
        });

        updateBubble(slider);
    });

    function updateVisibleBubbles() {
        sliders.forEach(updateBubble);
    }

    window.addEventListener('resize', updateVisibleBubbles);

    const softPanel = document.getElementById('soft-skill-step');
    const hardPanel = document.getElementById('hard-skill-step');
    const nextButton = document.getElementById('assessment-next');
    const backButton = document.getElementById('assessment-back');

    if (!softPanel || !hardPanel || !nextButton || !backButton) return;

    const submitButton = document.getElementById('assessment-submit');
    const currentStep = document.getElementById('assessment-current-step');

    function showStep(step) {
        const showHard = step === 'hard';
        softPanel.hidden = showHard;
        hardPanel.hidden = !showHard;
        nextButton.hidden = showHard;
        backButton.hidden = !showHard;
        submitButton.hidden = !showHard;
        softPanel.classList.toggle('hidden', showHard);
        hardPanel.classList.toggle('hidden', !showHard);
        nextButton.classList.toggle('hidden', showHard);
        backButton.classList.toggle('hidden', !showHard);
        submitButton.classList.toggle('hidden', !showHard);
        currentStep.textContent = showHard ? '2' : '1';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        requestAnimationFrame(updateVisibleBubbles);
    }

    nextButton.addEventListener('click', function() {
        showStep('hard');
    });
    backButton.addEventListener('click', function() {
        showStep('soft');
    });
});
</script>
@endif
@endsection

@push('styles')
<style>
    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #9333ea;
        cursor: pointer;
    }
    input[type="range"]::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #9333ea;
        cursor: pointer;
        border: none;
    }
    input[type="range"]::-webkit-slider-runnable-track {
        height: 6px;
        border-radius: 9999px;
        background: #e5e7eb;
    }
    input[type="range"]::-moz-range-track {
        height: 6px;
        border-radius: 9999px;
        background: #e5e7eb;
    }
    .dark input[type="range"]::-webkit-slider-runnable-track {
        background: #475569;
    }
    .dark input[type="range"]::-moz-range-track {
        background: #475569;
    }
    input[type="range"]:focus-visible {
        outline: 2px solid #7c3aed;
        outline-offset: 4px;
    }
</style>
@endpush
