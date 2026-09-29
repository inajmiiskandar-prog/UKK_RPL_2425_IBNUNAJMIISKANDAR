@extends('layouts.dashboard')

@section('title', 'Edit Port PON')
@section('page-title', 'Edit Port PON')
@section('page-breadcrumb', 'Master Data / Port PON / Edit')

@section('content')
<div class="mb-6">
    <a href="{{ route('masterdata.port-pon.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h2 class="mb-6 font-display text-lg font-semibold text-gray-800 dark:text-white">Form Edit Port PON</h2>

    @if(session('error'))
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">Terjadi kesalahan:</p>
        <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ session('error') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">Terjadi kesalahan:</p>
        <ul class="mt-1 list-inside list-disc text-sm text-red-600 dark:text-red-300">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('masterdata.port-pon.update', $portPon->id_port) }}" method="POST" class="space-y-5">
        @csrf @method('PUT')
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="id_olt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">OLT <span class="text-red-500">*</span></label>
                <select id="id_olt" name="id_olt" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    @foreach($olts as $olt)
                    <option value="{{ $olt->id_olt }}" {{ $portPon->id_olt == $olt->id_olt ? 'selected' : '' }}>{{ $olt->kode_olt }} - {{ $olt->nama_olt }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="nomor_port" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Nomor Port <span class="text-red-500">*</span></label>
                <input type="number" id="nomor_port" name="nomor_port" value="{{ old('nomor_port', $portPon->nomor_port) }}" min="1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="tipe_kartu" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Tipe Kartu <span class="text-red-500">*</span></label>
                <input type="text" id="tipe_kartu" name="tipe_kartu" value="{{ old('tipe_kartu', $portPon->tipe_kartu) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
            <div>
                <label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="TERSEDIA" {{ $portPon->status == 'TERSEDIA' ? 'selected' : '' }}>Tersedia</option>
                    <option value="TERPASANG" {{ $portPon->status == 'TERPASANG' ? 'selected' : '' }}>Terpasang</option>
                    <option value="RUSAK" {{ $portPon->status == 'RUSAK' ? 'selected' : '' }}>Rusak</option>
                </select>
            </div>
        </div>
        <div>
            <label for="id_odp" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">ODP (opsional)</label>
            <select id="id_odp" name="id_odp" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="">Pilih ODP</option>
                @foreach($odps as $odp)
                <option value="{{ $odp->id_odp }}" {{ old('id_odp', $portPon->id_odp) == $odp->id_odp ? 'selected' : '' }}>{{ $odp->kode_odp }} - {{ $odp->nama_odp }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-3 pt-4">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700 hover:shadow-xl">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Update
            </button>
            <a href="{{ route('masterdata.port-pon.index') }}" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">Batal</a>
        </div>
    </form>
</div>
@endsection
