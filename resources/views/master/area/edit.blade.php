@extends('layouts.dashboard')

@section('title', 'Edit Area')
@section('page-title', 'Edit Area')
@section('page-breadcrumb', 'Master Data / Area / Edit')

@section('content')
<div class="mb-6">
    <a href="{{ route('masterdata.area.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h2 class="mb-6 font-display text-lg font-semibold text-gray-800 dark:text-white">Form Edit Area</h2>

    @if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-red-800 dark:text-red-200">Terjadi kesalahan:</p>
                <ul class="mt-1 list-inside list-disc text-sm text-red-600 dark:text-red-300">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('masterdata.area.update', $area->id_area) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="kode_area" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                Kode Area
            </label>
            <input type="text" id="kode_area" value="{{ $area->kode_area }}" disabled
                   class="w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 py-3 text-sm text-gray-500 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-400">
        </div>

        <div>
            <label for="nama_area" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                Nama Area <span class="text-red-500">*</span>
            </label>
            <input type="text" id="nama_area" name="nama_area" value="{{ old('nama_area', $area->nama_area) }}"
                   placeholder="Masukkan nama area"
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-purple-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder:text-gray-400"
                   required>
        </div>

        <div>
            <label for="keterangan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                Keterangan
            </label>
            <textarea id="keterangan" name="keterangan" rows="3"
                      placeholder="Masukkan keterangan (opsional)"
                      class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-purple-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder:text-gray-400">{{ old('keterangan', $area->keterangan) }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-4">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 transition-all hover:bg-purple-700 hover:shadow-xl">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Update
            </button>
            <a href="{{ route('masterdata.area.index') }}" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
