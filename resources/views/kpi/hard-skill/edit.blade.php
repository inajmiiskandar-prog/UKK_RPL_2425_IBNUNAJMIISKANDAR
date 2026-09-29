@extends('layouts.dashboard')

@section('title', 'Edit Hard Skill')
@section('page-title', 'Edit Hard Skill')
@section('page-breadcrumb', 'KPI / Hard Skill / Edit')

@section('content')
<div class="mb-6">
    <a href="{{ route('kpi.hard-skill.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
</div>

<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h2 class="mb-6 font-display text-lg font-semibold text-gray-800 dark:text-white">Edit Hard Skill</h2>

    @if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
        <ul class="list-inside list-disc">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('kpi.hard-skill.update', $hardSkill->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="nama_indikator" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Deskripsi <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_indikator" id="nama_indikator" value="{{ old('nama_indikator', $hardSkill->nama_indikator) }}" required
                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
                <div>
                    <label for="deskripsi" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Keterangan
                    </label>
                    <input type="text" name="deskripsi" id="deskripsi" value="{{ old('deskripsi', $hardSkill->deskripsi) }}"
                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('kpi.hard-skill.index') }}" class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600">Batal</a>
            <button type="submit" class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">Simpan</button>
        </div>
    </form>
</div>
@endsection
