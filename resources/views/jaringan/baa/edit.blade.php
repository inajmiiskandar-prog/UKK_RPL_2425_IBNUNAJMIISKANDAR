@extends('layouts.dashboard')

@section('title', 'Edit BAA')
@section('page-title', 'Edit BAA')
@section('page-breadcrumb', 'Jaringan / BAA / Edit')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<style>
    .select2-container { width: 100% !important; }
</style>
@endpush

@section('content')
<div class="mb-6">
    <h1 class="font-display text-2xl font-bold text-gray-800 dark:text-white">Edit BAA Instalasi</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $baa->kode_baa }}</p>
</div>

@if(session('error'))
<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
    {{ session('error') }}
</div>
@endif

<form action="{{ route('jaringan.baa.update', $baa->id_baa) }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    {{-- Data BAA --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Data BAA</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pelanggan <span class="text-red-500">*</span></label>
                <select name="id_fab" class="select2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">-- Pilih Pelanggan --</option>
                    @foreach($fabs as $fab)
                    <option value="{{ $fab->id_fab }}" {{ old('id_fab', $baa->id_fab) == $fab->id_fab ? 'selected' : '' }}>
                        {{ $fab->kode_fab }} - {{ $fab->nama_pelanggan }}
                    </option>
                    @endforeach
                </select>
                @error('id_fab')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Instalasi <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_instalasi" value="{{ old('tanggal_instalasi', $baa->tanggal_instalasi->format('Y-m-d')) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                @error('tanggal_instalasi')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status <span class="text-red-500">*</span></label>
                <select name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="PENDING" {{ old('status', $baa->status) == 'PENDING' ? 'selected' : '' }}>Pending</option>
                    <option value="PROGRES" {{ old('status', $baa->status) == 'PROGRES' ? 'selected' : '' }}>Progres</option>
                    <option value="SELESAI" {{ old('status', $baa->status) == 'SELESAI' ? 'selected' : '' }}>Selesai</option>
                </select>
                @error('status')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                <textarea name="catatan" rows="1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">{{ old('catatan', $baa->catatan) }}</textarea>
                @error('catatan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Data Teknis --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Data Teknis</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">OLT <span class="text-red-500">*</span></label>
                <select name="id_olt" class="select2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">-- Pilih OLT --</option>
                    @foreach($olts as $olt)
                    <option value="{{ $olt->id_olt }}" {{ old('id_olt', $baa->id_olt) == $olt->id_olt ? 'selected' : '' }}>
                        {{ $olt->kode_olt }} - {{ $olt->nama_olt }}
                    </option>
                    @endforeach
                </select>
                @error('id_olt')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Port OLT <span class="text-red-500">*</span></label>
                <input type="number" name="port_olt" value="{{ old('port_olt', $baa->port_olt) }}" min="1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                @error('port_olt')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">ODP <span class="text-red-500">*</span></label>
                <select name="id_odp" class="select2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">-- Pilih ODP --</option>
                    @foreach($odps as $odp)
                    <option value="{{ $odp->id_odp }}" {{ old('id_odp', $baa->id_odp) == $odp->id_odp ? 'selected' : '' }}>
                        {{ $odp->kode_odp }} - {{ $odp->nama_odp }}
                    </option>
                    @endforeach
                </select>
                @error('id_odp')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Port ODP</label>
                <input type="number" name="port_odp" value="{{ old('port_odp', $baa->port_odp) }}" min="1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('port_odp')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">ONT <span class="text-red-500">*</span></label>
                <select name="id_ont" class="select2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">-- Pilih ONT --</option>
                    @foreach($onts as $ont)
                    <option value="{{ $ont->id_ont }}" {{ old('id_ont', $baa->id_ont) == $ont->id_ont ? 'selected' : '' }}>
                        {{ $ont->serial_number }} - {{ $ont->tipe }}
                    </option>
                    @endforeach
                </select>
                @error('id_ont')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Teknisi Utama <span class="text-red-500">*</span></label>
                <select name="id_user" class="select2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">-- Pilih Teknisi --</option>
                    @foreach($teknisis as $teknisi)
                    <option value="{{ $teknisi->id_user }}" {{ old('id_user', $baa->id_user) == $teknisi->id_user ? 'selected' : '' }}>
                        {{ $teknisi->nama }}
                    </option>
                    @endforeach
                </select>
                @error('id_user')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Data Ukuran --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Data Ukuran</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Ping (ms)</label>
                <input type="number" step="0.01" name="ping_ms" value="{{ old('ping_ms', $baa->ping_ms) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('ping_ms')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">RX Power (dBm)</label>
                <input type="number" step="0.01" name="rx_power_dbm" value="{{ old('rx_power_dbm', $baa->rx_power_dbm) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('rx_power_dbm')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">TX Power (dBm)</label>
                <input type="number" step="0.01" name="tx_power_dbm" value="{{ old('tx_power_dbm', $baa->tx_power_dbm) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('tx_power_dbm')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Speed Download</label>
                <input type="text" name="speed_download" value="{{ old('speed_download', $baa->speed_download) }}" placeholder="e.g. 100 Mbps" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('speed_download')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Speed Upload</label>
                <input type="text" name="speed_upload" value="{{ old('speed_upload', $baa->speed_upload) }}" placeholder="e.g. 100 Mbps" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('speed_upload')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Teknisi Tambahan --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Teknisi Tambahan</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Teknisi Tambahan</label>
                <select name="teknisi_ids[]" class="select2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" multiple>
                    @php
                        $selectedTambahans = old('teknisi_ids', $baa->teknisiTambahan->pluck('id_user')->toArray());
                    @endphp
                    @foreach($teknisis as $teknisi)
                    <option value="{{ $teknisi->id_user }}" {{ in_array($teknisi->id_user, $selectedTambahans) ? 'selected' : '' }}>{{ $teknisi->nama }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Tahan Ctrl/Cmd untuk memilih lebih dari satu</p>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('jaringan.baa.show', $baa->id_baa) }}" class="rounded-xl border border-gray-200 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">Batal</a>
        <button type="submit" class="rounded-xl bg-purple-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700">Simpan</button>
    </div>
</form>
@endsection
