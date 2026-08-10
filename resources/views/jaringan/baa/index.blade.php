@extends('layouts.dashboard')

@section('title', 'BAA')
@section('page-title', 'BAA Instalasi')
@section('page-breadcrumb', 'Jaringan / BAA')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-gray-800 dark:text-white">Data BAA Instalasi</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Berita Acara Aktifitas Sekolah</p>
    </div>
    <a href="{{ route('jaringan.baa.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah BAA
    </a>
</div>

@if(session('success'))<div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">{{ session('success') }}</div>@endif

<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <form method="GET" class="flex flex-col gap-4 sm:flex-row">
        <div class="flex-1">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode BAA, nama pelanggan..." class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>
        <select name="status" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            <option value="">Semua Status</option>
            <option value="SELESAI" {{ request('status') == 'SELESAI' ? 'selected' : '' }}>Selesai</option>
            <option value="PROGRES" {{ request('status') == 'PROGRES' ? 'selected' : '' }}>Progres</option>
            <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pending</option>
        </select>
        <button type="submit" class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">Cari</button>
    </form>
</div>

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-slate-700 dark:bg-slate-700/50">
                <tr>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Kode BAA</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Pelanggan</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Teknisi</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Tanggal</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    <th class="px-6 py-4 text-right font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @forelse($baas as $baa)
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700">
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full bg-pink-100 px-2.5 py-0.5 text-xs font-semibold text-pink-700 dark:bg-pink-900/30 dark:text-pink-400">{{ $baa->kode_baa }}</span></td>
                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">{{ $baa->fab->nama_pelanggan ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $baa->teknisi->nama ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $baa->tanggal_instalasi->format('d M Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $baa->status == 'SELESAI' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($baa->status == 'PROGRES' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400') }}">
                            {{ $baa->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('jaringan.baa.show', $baa->id_baa) }}" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-purple-600 dark:text-gray-400 dark:hover:bg-slate-700"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                            <a href="{{ route('jaringan.baa.edit', $baa->id_baa) }}" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-slate-700"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center"><p class="text-gray-500 dark:text-gray-400">Belum ada data BAA</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($baas->hasPages())<div class="border-t border-gray-100 px-6 py-4 dark:border-slate-700">{{ $baas->withQueryString()->links() }}</div>@endif
</div>
@endsection
