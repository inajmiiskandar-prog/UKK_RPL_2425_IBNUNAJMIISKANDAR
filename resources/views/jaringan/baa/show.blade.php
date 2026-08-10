@extends('layouts.dashboard')

@section('title', 'Detail BAA')
@section('page-title', 'Detail BAA')
@section('page-breadcrumb', 'Jaringan / BAA / Detail')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-gray-800 dark:text-white">Detail BAA</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $baa->kode_baa }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('jaringan.baa.edit', $baa->id_baa) }}" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-blue-700">
            Edit
        </a>
        <a href="{{ route('jaringan.baa.index') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
            Kembali
        </a>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    {{-- Data Pelanggan --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Data Pelanggan</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Kode FAB</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->fab->kode_fab ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Nama Pelanggan</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->fab->nama_pelanggan ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Alamat</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white text-right max-w-[200px]">{{ $baa->fab->alamat ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Area</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->fab->area->nama_area ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Paket</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->fab->paket->nama_paket ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- Data BAA --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Data BAA</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Kode BAA</span>
                <span class="text-sm font-bold text-purple-600">{{ $baa->kode_baa }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Tanggal Instalasi</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->tanggal_instalasi->format('d M Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Status</span>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $baa->status == 'SELESAI' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($baa->status == 'PROGRES' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400') }}">
                    {{ $baa->status }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Teknisi Utama</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->teknisi->nama ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Catatan</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white text-right max-w-[200px]">{{ $baa->catatan ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- Data Teknis --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Data Teknis</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">OLT</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->olt->kode_olt ?? '-' }} - {{ $baa->olt->nama_olt ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Port OLT</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->port_olt }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">ODP</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->odp->kode_odp ?? '-' }} - {{ $baa->odp->nama_odp ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Port ODP</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->port_odp ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">ONT</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->ont->serial_number ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- Data Ukuran --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Data Ukuran</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Ping</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->ping_ms ? $baa->ping_ms . ' ms' : '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">RX Power</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->rx_power_dbm ? $baa->rx_power_dbm . ' dBm' : '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">TX Power</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->tx_power_dbm ? $baa->tx_power_dbm . ' dBm' : '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Speed Download</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->speed_download ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-500">Speed Upload</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $baa->speed_upload ?? '-' }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Teknisi Tambahan --}}
@if($baa->teknisiTambahan->count() > 0)
<div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Teknisi Tambahan</h3>
    <div class="flex flex-wrap gap-2">
        @foreach($baa->teknisiTambahan as $teknisi)
        <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
            {{ $teknisi->user->nama ?? '-' }}
        </span>
        @endforeach
    </div>
</div>
@endif

{{-- Material --}}
@if($baa->details->count() > 0)
<div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Material Yang Digunakan</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-slate-700 dark:bg-slate-700/50">
                <tr>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Material</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Jumlah</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @foreach($baa->details as $detail)
                <tr>
                    <td class="px-4 py-3 text-gray-800 dark:text-white">{{ $detail->material->nama_material ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-800 dark:text-white">{{ $detail->jumlah }} {{ $detail->material->satuan ?? '' }}</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $detail->keterangan ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
