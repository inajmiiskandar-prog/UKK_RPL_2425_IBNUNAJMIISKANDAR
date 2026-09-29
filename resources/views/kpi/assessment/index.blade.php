@extends('layouts.dashboard')

@section('title', 'Penilaian KPI')
@section('page-title', 'Penilaian KPI')
@section('page-breadcrumb', 'KPI / Penilaian')

@section('content')
{{-- Flash Messages --}}
@if(session('success'))<div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">{{ session('error') }}</div>@endif

{{-- Info Periode Aktif --}}
@if(isset($activePeriod) && $activePeriod)
<div class="mb-6 rounded-xl border border-purple-200 bg-purple-50 p-4 dark:border-purple-900 dark:bg-purple-900/20">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-purple-700 dark:text-purple-300">Periode Aktif: {{ $activePeriod->nama }}</h3>
            <p class="text-sm text-purple-600 dark:text-purple-400">
                {{ $activePeriod->tanggal_mulai->format('d/m/Y') }} - {{ $activePeriod->tanggal_selesai->format('d/m/Y') }}
            </p>
        </div>
        <span class="rounded-full bg-purple-200 px-3 py-1 text-sm font-medium text-purple-700 dark:bg-purple-800 dark:text-purple-300">Aktif</span>
    </div>
</div>
@endif

<div class="grid gap-6 lg:grid-cols-2">
    {{-- Self Assessment Section --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="font-display text-lg font-semibold text-gray-800 dark:text-white">Self Assessment Saya</h3>
            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                Self Assessment
            </span>
        </div>

        @if($myAssessment && $myAssessment->isSelfDone())
        <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
            <div class="flex items-center gap-2 text-green-700 dark:text-green-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">
                    {{ $myAssessment->status === 'menunggu_review' ? 'Menunggu review atasan' : ($myAssessment->status === 'sudah_dicek' ? 'Sudah selesai dinilai - Skor: ' . number_format($myAssessment->skor_akhir, 1) : 'Self Assessment sudah selesai') }}
                </span>
            </div>
            @if($myAssessment->skor_akhir !== null)
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Skor akhir: <strong>{{ number_format($myAssessment->skor_akhir, 1) }}</strong></p>
            @else
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Skor akhir tersedia setelah review atasan.</p>
            @endif
        </div>
        @else
        <div class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
            <p class="text-sm text-yellow-700 dark:text-yellow-400">
                Self Assessment belum dilakukan. Silakan isi form penilaian untuk diri sendiri.
            </p>
        </div>
        @endif
        {{-- Arahkan ke Wizard Step 1 untuk self assessment --}}
        <a href="{{ route('kpi.assessment.self') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
            {{ $myAssessment && $myAssessment->isSelfDone() ? 'Edit' : 'Isi' }} Self Assessment
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    {{-- Penilaian Atasan Section (jika punya bawahan) --}}
    @if($hasBawahan)
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="font-display text-lg font-semibold text-gray-800 dark:text-white">Penilaian Bawahan</h3>
            <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-medium text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                Penilaian Atasan
            </span>
        </div>

        @if($bawahans->count() > 0)
        <div class="space-y-3">
            @foreach($bawahans as $bawahan)
                @php
                    // Ambil assessment bawahan di periode aktif
                    $bawahanAssessment = isset($activePeriod) ? $activePeriod->assessments->where('user_id', $bawahan->id_user)->first() : null;
                    $statusClass = match($bawahanAssessment?->status ?? 'pending') {
                        'pending' => 'bg-gray-100 text-gray-600',
                        'self_done' => 'bg-yellow-100 text-yellow-700',
                        'atasan_done' => 'bg-orange-100 text-orange-700',
                        'selesai' => 'bg-green-100 text-green-700',
                        'menunggu_review' => 'bg-yellow-100 text-yellow-700',
                        'sudah_dicek' => 'bg-green-100 text-green-700',
                    };
                    $statusLabel = match($bawahanAssessment?->status ?? 'pending') {
                        'pending' => 'Belum',
                        'self_done' => 'Self Selesai',
                        'atasan_done' => 'Atasan Selesai',
                        'selesai' => 'Selesai',
                        'menunggu_review' => 'Menunggu Review',
                        'sudah_dicek' => 'Sudah Dicek',
                    };
                @endphp
                <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3 dark:border-slate-700">
                    <div>
                        <p class="font-medium text-gray-800 dark:text-white">{{ $bawahan->nama }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $bawahan->divisi ?? '-' }} / {{ $bawahan->jabatan ?? '-' }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                        @if($bawahanAssessment?->status === 'menunggu_review')
                        <a href="{{ route('kpi.assessment.create', ['assessment_id' => $bawahanAssessment->id]) }}" class="rounded-lg bg-orange-100 px-3 py-1.5 text-xs font-medium text-orange-700 hover:bg-orange-200 dark:bg-orange-900/30 dark:text-orange-400">
                            Review
                        </a>
                        @elseif($bawahanAssessment?->isAtasanDone())
                        <a href="{{ route('kpi.assessment.show', $bawahanAssessment->id) }}" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-slate-700 dark:text-gray-300">
                            Lihat
                        </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada bawahan yang perlu dinilai.</p>
        @endif
    </div>
    @endif
</div>

{{-- Link ke Halaman Lain --}}
<div class="mt-6 flex flex-wrap gap-3">
    <a href="{{ route('kpi.assessment.history') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-300 dark:hover:bg-slate-700">
        Histori KPI Saya
    </a>
    @if(auth()->user()->role === 'ADMIN')
    <a href="{{ route('kpi.assessment.recap') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-300 dark:hover:bg-slate-700">
        Rekap Semua Karyawan
    </a>
    @endif
</div>
@endsection
