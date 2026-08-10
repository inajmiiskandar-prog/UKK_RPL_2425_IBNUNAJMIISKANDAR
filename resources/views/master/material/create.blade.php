@extends('layouts.dashboard')

@section('title', 'Tambah Material')
@section('page-title', 'Tambah Material')
@section('page-breadcrumb', 'Master Data / Material / Tambah')

@section('content')
<div class="mb-6">
    <a href="{{ route('masterdata.material.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h2 class="mb-6 font-display text-lg font-semibold text-gray-800 dark:text-white">Form Tambah Material</h2>

    @if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">Terjadi kesalahan:</p>
        <ul class="mt-1 list-inside list-disc text-sm text-red-600 dark:text-red-300">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('masterdata.material.store') }}" method="POST" class="space-y-5">
        @csrf
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="nama_material" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Nama Material <span class="text-red-500">*</span></label>
                <input type="text" id="nama_material" name="nama_material" value="{{ old('nama_material') }}" placeholder="Contoh: Kabel FO 2 Core" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
            <div>
                <label for="kondisi" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Kondisi <span class="text-red-500">*</span></label>
                <select id="kondisi" name="kondisi" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="BAIK" {{ old('kondisi') == 'BAIK' ? 'selected' : '' }}>Baik</option>
                    <option value="RUSAK" {{ old('kondisi') == 'RUSAK' ? 'selected' : '' }}>Rusak</option>
                </select>
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-3">
            <div>
                <label for="stok" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Stok <span class="text-red-500">*</span></label>
                <input type="number" id="stok" name="stok" value="{{ old('stok', 0) }}" min="0" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
            <div>
                <label for="minimal_stok" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Minimal Stok <span class="text-red-500">*</span></label>
                <input type="number" id="minimal_stok" name="minimal_stok" value="{{ old('minimal_stok', 5) }}" min="0" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
            <div>
                <label for="satuan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Satuan <span class="text-red-500">*</span></label>
                <input type="text" id="satuan" name="satuan" value="{{ old('satuan') }}" placeholder="pcs, meter, roll" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
        </div>
        <div>
            <label for="harga" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Harga <span class="text-red-500">*</span></label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                <input type="number" id="harga" name="harga" value="{{ old('harga') }}" min="0" placeholder="50000" class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
        </div>
        <div>
            <label for="keterangan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="2" placeholder="Keterangan tambahan (opsional)" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">{{ old('keterangan') }}</textarea>
        </div>
        <div class="flex items-center gap-3 pt-4">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700 hover:shadow-xl">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan
            </button>
            <a href="{{ route('masterdata.material.index') }}" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">Batal</a>
        </div>
    </form>
</div>
@endsection
