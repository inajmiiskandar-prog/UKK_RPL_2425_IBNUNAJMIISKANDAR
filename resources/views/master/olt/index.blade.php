@extends('layouts.dashboard')

@section('title', 'OLT')
@section('page-title', 'OLT')
@section('page-breadcrumb', 'Master Data / OLT')

@push('styles')
<style>
    .table-hover:hover { background-color: #f8fafc; }
    .dark .table-hover:hover { background-color: #1e293b; }
</style>
@endpush

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-gray-800 dark:text-white">Data OLT</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola Optical Line Terminal</p>
    </div>
    <a href="{{ route('masterdata.olt.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 transition-all hover:bg-purple-700 hover:shadow-xl">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah OLT
    </a>
</div>

{{-- Alerts --}}
@if(session('success'))
<div class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    {{ session('error') }}
</div>
@endif

{{-- Search & Filter --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <form method="GET" action="{{ route('masterdata.olt.index') }}" class="flex flex-col gap-4 sm:flex-row">
        <div class="flex-1">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari OLT..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-purple-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder:text-gray-400">
                <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>
        <select name="pop" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            <option value="">Semua POP</option>
            @foreach($pops as $pop)
            <option value="{{ $pop->id_pop }}" {{ request('pop') == $pop->id_pop ? 'selected' : '' }}>{{ $pop->nama_pop }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">
            Cari
        </button>
        @if(request('search') || request('pop'))
        <a href="{{ route('masterdata.olt.index') }}" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
            Reset
        </a>
        @endif
    </form>
</div>

{{-- Export Buttons --}}
<div class="mb-6 flex flex-wrap items-center justify-end gap-3">
    <a href="{{ route('masterdata.olt.exportCsv', request()->query()) }}"
       class="inline-flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 transition-colors hover:bg-green-100 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/40">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Export CSV
    </a>
    <a href="{{ route('masterdata.olt.exportExcel', request()->query()) }}"
       class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Export Excel
    </a>
</div>

{{-- Table --}}
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-slate-700 dark:bg-slate-700/50">
                <tr>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Kode</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Nama OLT</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">POP</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Lokasi</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">IP Address</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Tanggal Dibuat</th>
                    <th class="px-6 py-4 text-right font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @forelse($olts as $olt)
                <tr class="table-hover">
                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">{{ $olt->kode_olt }}</td>
                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">{{ $olt->nama_olt }}</td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $olt->pop->nama_pop ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $olt->lokasi ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $olt->ip_olt ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $olt->createdAt?->format('d/m/Y') ?? '-' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('masterdata.olt.show', $olt->id_olt) }}" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-purple-600 dark:text-gray-400 dark:hover:bg-slate-700" title="Detail">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('masterdata.olt.edit', $olt->id_olt) }}" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-slate-700" title="Edit">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @if(auth()->user()->role === 'ADMIN')
                            <form action="{{ route('masterdata.olt.destroy', $olt->id_olt) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Yakin hapus OLT {{ $olt->nama_olt }}?')" class="rounded-lg p-2 text-gray-500 hover:bg-red-50 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-900/20 dark:hover:text-red-400" title="Hapus">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                        </svg>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Belum ada data OLT</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($olts->hasPages())
    <div class="border-t border-gray-100 px-6 py-4 dark:border-slate-700">
        {{ $olts->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
