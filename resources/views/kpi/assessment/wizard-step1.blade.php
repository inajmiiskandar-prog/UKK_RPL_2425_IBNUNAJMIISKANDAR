@extends('layouts.dashboard')

@section('title', 'Wizard Penilaian KPI - Step 1')
@section('page-title', 'Wizard Penilaian KPI')
@section('page-breadcrumb', 'KPI / Penilaian / Wizard')

@section('content')
<div class="mb-6">
    <a href="{{ route('kpi.assessment.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
</div>

{{-- Progress Indicator --}}
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-600 text-sm font-bold text-white">1</div>
            <span class="font-medium text-purple-600 dark:text-purple-400">Periode & Karyawan</span>
        </div>
        <div class="flex-1 h-1 mx-4 bg-gray-200 dark:bg-slate-700"><div class="h-full w-0 bg-purple-600"></div></div>
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-sm font-bold text-gray-500 dark:bg-slate-700 dark:text-gray-400">2</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Soft Skill</span>
        </div>
        <div class="flex-1 h-1 mx-4 bg-gray-200 dark:bg-slate-700"><div class="h-full w-0 bg-gray-200 dark:bg-slate-700"></div></div>
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
    <h3 class="font-semibold text-blue-700 dark:text-blue-300">Step 1: Konfirmasi Review Atasan</h3>
    <p class="mt-1 text-sm text-blue-600 dark:text-blue-400">Konfirmasi assessment self karyawan sebelum mulai review.</p>
</div>

@if(session('error'))<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">{{ session('error') }}</div>@endif

@if($errors->any())
<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
    <ul class="list-inside list-disc">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <form action="{{ route('kpi.assessment.wizard.step1') }}" method="POST" id="wizard-form">
        @csrf
        @if($reviewMode ?? false)
        <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">
        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <p>Periode: <strong>{{ $assessment->period?->nama ?? '-' }}</strong></p>
            <p>Karyawan: <strong>{{ $assessment->user->nama }}</strong></p>
            <p>Status: <strong>Menunggu review atasan</strong></p>
        </div>
        @else
        <div class="space-y-6">
            {{-- Pilih Periode --}}
            <div>
                <label for="period_value" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Periode Penilaian (YYYY-MM) <span class="text-red-500">*</span>
                </label>
                <input type="month" name="period_value" id="period_value" required
                       value="{{ old('period_value', $wizardData['period_value'] ?? '') }}"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('period_value')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Pilih Karyawan --}}
            <div>
                <label for="user_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Karyawan <span class="text-red-500">*</span>
                </label>
                <select name="user_id" id="user_id" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id_user }}" {{ old('user_id', $wizardData['user_id'] ?? '') == $emp->id_user ? 'selected' : '' }}>
                        {{ $emp->nama }} - {{ $emp->divisi ?? '-' }} / {{ $emp->jabatan ?? '-' }}
                        @if($emp->id_user === auth()->user()?->id_user)
                            (Saya)
                        @endif
                    </option>
                    @endforeach
                </select>
                @error('user_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    @if($hasBawahan)
                    Anda dapat memilih bawahan atau menilai diri sendiri.
                    @else
                    Anda hanya dapat menilai diri sendiri.
                    @endif
                </p>
            </div>
        </div>

        @endif

        <div class="mt-8 flex justify-end">
            <button type="submit" class="rounded-xl bg-purple-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">
                Selanjutnya
                <svg class="ml-2 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </form>
</div>
@endsection
