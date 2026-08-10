@extends('layouts.dashboard')

@section('title', 'ODP')
@section('page-title', 'ODP')
@section('page-breadcrumb', 'Master Data / ODP')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-gray-800 dark:text-white">Data ODP</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola Optical Distribution Point</p>
    </div>
    <a href="{{ route('masterdata.odp.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah ODP
    </a>
</div>

@if(session('success'))<div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">{{ session('error') }}</div>@endif

<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <form method="GET" class="flex flex-col gap-4 sm:flex-row">
        <div class="flex-1">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ODP..." class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>
        <select name="olt" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            <option value="">Semua OLT</option>
            @foreach($olts as $olt)
            <option value="{{ $olt->id_olt }}" {{ request('olt') == $olt->id_olt ? 'selected' : '' }}>{{ $olt->nama_olt }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">Cari</button>
    </form>
</div>

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-slate-700 dark:bg-slate-700/50">
                <tr>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Kode</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Nama ODP</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">OLT</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Port</th>
                    <th class="px-6 py-4 text-right font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @forelse($odps as $odp)
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700">
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-semibold text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">{{ $odp->kode_odp }}</span></td>
                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">{{ $odp->nama_odp }}</td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $odp->olt->nama_olt ?? '-' }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">{{ $odp->jumlah_port ?? 0 }} Port</span></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('masterdata.odp.show', $odp->id_odp) }}" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-purple-600 dark:text-gray-400 dark:hover:bg-slate-700"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                            <a href="{{ route('masterdata.odp.edit', $odp->id_odp) }}" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-slate-700"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <form action="{{ route('masterdata.odp.destroy', $odp->id_odp) }}" method="POST" class="inline">@csrf @method('DELETE')<button type="submit" onclick="return confirm('Yakin hapus ODP {{ $odp->nama_odp }}?')" class="rounded-lg p-2 text-gray-500 hover:bg-red-50 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-900/20 dark:hover:text-red-400"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-12 text-center"><p class="text-gray-500 dark:text-gray-400">Belum ada data ODP</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($odps->hasPages())<div class="border-t border-gray-100 px-6 py-4 dark:border-slate-700">{{ $odps->withQueryString()->links() }}</div>@endif
</div>
@endsection
