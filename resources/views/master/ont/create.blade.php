@extends('layouts.dashboard')

@section('title', 'Tambah ONT')
@section('page-title', 'Tambah ONT')
@section('page-breadcrumb', 'Master Data / ONT / Tambah')

@section('content')
<div class="mb-6">
    <a href="{{ route('masterdata.ont.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h2 class="mb-6 font-display text-lg font-semibold text-gray-800 dark:text-white">Form Tambah ONT</h2>

    @if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">Terjadi kesalahan:</p>
        <ul class="mt-1 list-inside list-disc text-sm text-red-600 dark:text-red-300">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('masterdata.ont.store') }}" method="POST" class="space-y-5">
        @csrf
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="serial_number" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Serial Number <span class="text-red-500">*</span></label>
                <input type="text" id="serial_number" name="serial_number" value="{{ old('serial_number') }}" placeholder="Masukkan serial number" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-mono focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
            <div>
                <label for="pelanggan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Pelanggan <span class="text-red-500">*</span></label>
                <input type="text" id="pelanggan" name="pelanggan" value="{{ old('pelanggan') }}" placeholder="Nama pelanggan" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="TERSEDIA" {{ old('status') == 'TERSEDIA' ? 'selected' : '' }}>Tersedia</option>
                    <option value="TERPASANG" {{ old('status') == 'TERPASANG' ? 'selected' : '' }}>Terpasang</option>
                    <option value="RUSAK" {{ old('status') == 'RUSAK' ? 'selected' : '' }}>Rusak</option>
                </select>
            </div>
            <div>
                <label for="id_pop" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">POP <span class="text-red-500">*</span></label>
                <select id="id_pop" name="id_pop" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">Pilih POP</option>
                    @foreach($pops as $pop)
                    <option value="{{ $pop->id_pop }}" {{ old('id_pop') == $pop->id_pop ? 'selected' : '' }}>{{ $pop->kode_pop }} - {{ $pop->nama_pop }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label for="id_odp" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">ODP (opsional)</label>
            <select id="id_odp" name="id_odp" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="">Pilih ODP</option>
                @foreach($odps as $odp)
                <option value="{{ $odp->id_odp }}" {{ old('id_odp') == $odp->id_odp ? 'selected' : '' }}>{{ $odp->kode_odp }} - {{ $odp->nama_odp }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-3 pt-4">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700 hover:shadow-xl">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan
            </button>
            <a href="{{ route('masterdata.ont.index') }}" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">Batal</a>
        </div>
    </form>
</div>
@endsection
