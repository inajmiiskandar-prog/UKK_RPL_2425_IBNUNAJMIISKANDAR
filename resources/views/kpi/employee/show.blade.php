@extends('layouts.dashboard')

@section('title', 'Detail Karyawan')
@section('page-title', 'Detail Karyawan')
@section('page-breadcrumb', 'KPI / Data Karyawan / Detail')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('kpi.employee.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
    @if(auth()->user()->role === 'ADMIN')
    <div class="flex gap-2">
        <form action="{{ route('kpi.employee.toggle-status', $employee->id_user) }}" method="POST" class="inline">
            @csrf
            <button type="submit" onclick="return confirm('Yakin {{ $employee->status ? 'nonaktifkan' : 'aktifkan' }} karyawan ini?')" class="rounded-lg border px-4 py-2 text-sm font-medium {{ $employee->status ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400' : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100 dark:border-green-900 dark:bg-green-900/20 dark:text-green-400' }}">
                {{ $employee->status ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
        </form>
        <form action="{{ route('kpi.employee.reset-password', $employee->id_user) }}" method="POST" class="inline">
            @csrf
            <button type="submit" onclick="return confirm('Reset password untuk {{ $employee->nama }}? Password baru akan ditampilkan setelah reset.')" class="rounded-lg border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-medium text-orange-700 hover:bg-orange-100 dark:border-orange-900 dark:bg-orange-900/20 dark:text-orange-400">
                Reset Password
            </button>
        </form>
        <a href="{{ route('kpi.employee.edit', $employee->id_user) }}" class="rounded-lg bg-blue-100 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400">
            Edit Data
        </a>
    </div>
    @endif
</div>

{{-- Info Password Baru --}}
@if(session('new_password'))
<div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
    <div class="flex items-start gap-3">
        <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        <div>
            <h4 class="font-semibold text-green-700 dark:text-green-300">Password Baru!</h4>
            <p class="mt-2 inline-block rounded-lg bg-white px-4 py-2 font-mono text-lg font-bold text-green-700 dark:bg-green-900/30 dark:text-green-300">
                {{ session('new_password') }}
            </p>
        </div>
    </div>
</div>
@endif

{{-- Info Karyawan --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="flex items-start gap-6">
        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-purple-100 text-3xl font-bold text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
            {{ substr($employee->nama, 0, 1) }}
        </div>
        <div class="flex-1">
            <h2 class="font-display text-2xl font-bold text-gray-800 dark:text-white">{{ $employee->nama }}</h2>
            <p class="mt-1 text-gray-500 dark:text-gray-400">{{ $employee->jabatan ?? '-' }} / {{ $employee->divisi ?? '-' }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <span class="rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">{{ $employee->role }}</span>
                @if($employee->status)
                <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">Aktif</span>
                @else
                <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">Nonaktif</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    {{-- Info Detail --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Informasi Karyawan</h3>
        <dl class="space-y-3">
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">NIK</dt>
                <dd class="font-medium text-gray-800 dark:text-white">{{ $employee->nik ?? '-' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Telepon</dt>
                <dd class="font-medium text-gray-800 dark:text-white">{{ $employee->no_hp ?? '-' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Alamat</dt>
                <dd class="font-medium text-gray-800 dark:text-white">{{ $employee->alamat ?? '-' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Tanggal Masuk</dt>
                <dd class="font-medium text-gray-800 dark:text-white">{{ $employee->tanggal_masuk ? \Carbon\Carbon::parse($employee->tanggal_masuk)->format('d/m/Y') : '-' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Atasan</dt>
                <dd class="font-medium text-gray-800 dark:text-white">{{ $employee->atasan->nama ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Statistik KPI --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Statistik KPI</h3>
        <dl class="space-y-3">
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Total Penilaian</dt>
                <dd class="font-medium text-gray-800 dark:text-white">{{ $stats['total_assessments'] }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Selesai</dt>
                <dd class="font-medium text-green-600 dark:text-green-400">{{ $stats['completed'] }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Rata-rata Skor</dt>
                <dd class="font-medium text-purple-600 dark:text-purple-400">
                    {{ $stats['avg_score'] !== null ? number_format($stats['avg_score'], 1) : '-' }}
                </dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Skor Terbaik</dt>
                <dd class="font-medium text-gray-800 dark:text-white">
                    {{ $stats['best_score'] !== null ? number_format($stats['best_score'], 1) : '-' }}
                </dd>
            </div>
        </dl>
    </div>
</div>

{{-- Histori KPI --}}
<div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Histori KPI</h3>
    @if($kpiHistory->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 dark:border-slate-700">
                <tr>
                    <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Periode</th>
                    <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Skor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @foreach($kpiHistory as $h)
                <tr>
                    <td class="px-4 py-3 text-gray-800 dark:text-white">{{ $h->created_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        @php
                            $sClass = match($h->status) {
                                'selesai' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $sClass }}">{{ $h->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-purple-600 dark:text-purple-400">
                        {{ $h->skor_akhir !== null ? number_format($h->skor_akhir, 1) : '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="text-center text-gray-500 dark:text-gray-400">Belum ada histori KPI</p>
    @endif
</div>
@endsection
