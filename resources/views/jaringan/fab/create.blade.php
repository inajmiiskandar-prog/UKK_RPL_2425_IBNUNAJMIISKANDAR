@extends('layouts.dashboard')

@section('title', 'Tambah Pelanggan')
@section('page-title', 'Tambah Pelanggan')
@section('page-breadcrumb', 'Jaringan / Pelanggan / Tambah')

@section('content')
<div class="mb-6">
    <a href="{{ route('jaringan.fab.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h2 class="mb-6 font-display text-lg font-semibold text-gray-800 dark:text-white">Form Tambah Pelanggan</h2>

    @if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">Terjadi kesalahan:</p>
        <ul class="mt-1 list-inside list-disc text-sm text-red-600 dark:text-red-300">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('jaringan.fab.store') }}" method="POST" class="space-y-5">
        @csrf
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="nama_pelanggan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Nama Pelanggan <span class="text-red-500">*</span></label>
                <input type="text" id="nama_pelanggan" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}" placeholder="Nama lengkap" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
            <div>
                <label for="nik" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">NIK <span class="text-red-500">*</span></label>
                <input type="text" id="nik" name="nik" value="{{ old('nik') }}" placeholder="Nomor KTP" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="no_hp" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">No. HP <span class="text-red-500">*</span></label>
                <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
            <div>
                <label for="id_area" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Area <span class="text-red-500">*</span></label>
                <select id="id_area" name="id_area" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">Pilih Area</option>
                    @foreach($areas as $area)
                    <option value="{{ $area->id_area }}" {{ old('id_area') == $area->id_area ? 'selected' : '' }}>{{ $area->nama_area }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="id_paket" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Paket <span class="text-red-500">*</span></label>
                <select id="id_paket" name="id_paket" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">Pilih Paket</option>
                    @foreach($pakets as $paket)
                    <option value="{{ $paket->id_paket }}" {{ old('id_paket') == $paket->id_paket ? 'selected' : '' }}>{{ $paket->nama_paket }} - {{ $paket->kecepatan }} (Rp {{ number_format($paket->harga, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="OPEN" {{ old('status') == 'OPEN' ? 'selected' : '' }}>Open (Belum Aktif)</option>
                    <option value="AKTIF" {{ old('status') == 'AKTIF' ? 'selected' : '' }}>Aktif</option>
                </select>
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="id_user" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Sales</label>
                <select id="id_user" name="id_user" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    <option value="">Pilih Sales</option>
                    @foreach($sales as $s)
                    <option value="{{ $s->id_user }}" {{ (old('id_user', $selectedSales ?? null) == $s->id_user) ? 'selected' : '' }}>{{ $s->nama }}</option>
                    @endforeach
                </select>
                @if(auth()->user()->role === 'SALES')
                <p class="mt-1 text-xs text-gray-500">Otomatis dipilih berdasarkan akun login</p>
                @endif
            </div>
        </div>
        <div>
            <label for="alamat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Alamat <span class="text-red-500">*</span></label>
            <textarea id="alamat" name="alamat" rows="2" placeholder="Alamat lengkap" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>{{ old('alamat') }}</textarea>
        </div>
        <div class="flex items-center gap-3 pt-4">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700 hover:shadow-xl">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan
            </button>
            <a href="{{ route('jaringan.fab.index') }}" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">Batal</a>
        </div>
    </form>
</div>
@endsection
