@extends('layouts.dashboard')

@section('title', 'Tambah BAA')
@section('page-title', 'Tambah BAA')
@section('page-breadcrumb', 'Jaringan / BAA / Tambah')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<style>
    .select2-container { width: 100% !important; }
</style>
@endpush

@section('content')
<div class="mb-6">
    <h1 class="font-display text-2xl font-bold text-gray-800 dark:text-white">Tambah BAA Instalasi</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400">Tambah data berita acara aktivas sekolah</p>
</div>

@if(session('error'))
<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
    {{ session('error') }}
</div>
@endif

<form action="{{ route('jaringan.baa.store') }}" method="POST" class="space-y-6">
    @csrf

    {{-- Data Pelanggan --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Data Pelanggan</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pelanggan <span class="text-red-500">*</span></label>
                <select name="id_fab" id="id_fab" class="select2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">-- Pilih Pelanggan --</option>
                    @foreach($fabs as $fab)
                    <option value="{{ $fab->id_fab }}" {{ old('id_fab') == $fab->id_fab ? 'selected' : '' }}>
                        {{ $fab->kode_fab }} - {{ $fab->nama_pelanggan }} ({{ $fab->area->nama_area ?? '-' }})
                    </option>
                    @endforeach
                </select>
                @error('id_fab')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Instalasi <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_instalasi" value="{{ old('tanggal_instalasi', date('Y-m-d')) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                @error('tanggal_instalasi')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status <span class="text-red-500">*</span></label>
                <select name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="PENDING" {{ old('status') == 'PENDING' ? 'selected' : '' }}>Pending</option>
                    <option value="PROGRES" {{ old('status') == 'PROGRES' ? 'selected' : '' }}>Progres</option>
                    <option value="SELESAI" {{ old('status') == 'SELESAI' ? 'selected' : '' }}>Selesai</option>
                </select>
                @error('status')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                <textarea name="catatan" rows="1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">{{ old('catatan') }}</textarea>
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
                <select name="id_olt" id="id_olt" class="select2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">-- Pilih OLT --</option>
                    @foreach($olts as $olt)
                    <option value="{{ $olt->id_olt }}" {{ old('id_olt') == $olt->id_olt ? 'selected' : '' }}>
                        {{ $olt->kode_olt }} - {{ $olt->nama_olt }}
                    </option>
                    @endforeach
                </select>
                @error('id_olt')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Port OLT <span class="text-red-500">*</span></label>
                <input type="number" name="port_olt" value="{{ old('port_olt') }}" min="1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                @error('port_olt')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">ODP <span class="text-red-500">*</span></label>
                <select name="id_odp" id="id_odp" class="select2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">-- Pilih ODP --</option>
                    @foreach($odps as $odp)
                    <option value="{{ $odp->id_odp }}" {{ old('id_odp') == $odp->id_odp ? 'selected' : '' }}>
                        {{ $odp->kode_odp }} - {{ $odp->nama_odp }}
                    </option>
                    @endforeach
                </select>
                @error('id_odp')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Port ODP</label>
                <input type="number" name="port_odp" value="{{ old('port_odp') }}" min="1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('port_odp')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">ONT <span class="text-red-500">*</span></label>
                <select name="id_ont" id="id_ont" class="select2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">-- Pilih ONT --</option>
                    @foreach($onts as $ont)
                    <option value="{{ $ont->id_ont }}" {{ old('id_ont') == $ont->id_ont ? 'selected' : '' }}>
                        {{ $ont->serial_number }} - {{ $ont->tipe }}
                    </option>
                    @endforeach
                </select>
                @error('id_ont')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Data Ukuran --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Data Ukuran</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Ping (ms)</label>
                <input type="number" step="0.01" name="ping_ms" value="{{ old('ping_ms') }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('ping_ms')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">RX Power (dBm)</label>
                <input type="number" step="0.01" name="rx_power_dbm" value="{{ old('rx_power_dbm') }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('rx_power_dbm')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">TX Power (dBm)</label>
                <input type="number" step="0.01" name="tx_power_dbm" value="{{ old('tx_power_dbm') }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('tx_power_dbm')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Speed Download</label>
                <input type="text" name="speed_download" value="{{ old('speed_download') }}" placeholder="e.g. 100 Mbps" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('speed_download')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Speed Upload</label>
                <input type="text" name="speed_upload" value="{{ old('speed_upload') }}" placeholder="e.g. 100 Mbps" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('speed_upload')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Teknisi --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Teknisi</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Teknisi Utama <span class="text-red-500">*</span></label>
                <select name="id_user" class="select2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="">-- Pilih Teknisi Utama --</option>
                    @foreach($teknisis as $teknisi)
                    <option value="{{ $teknisi->id_user }}" {{ old('id_user') == $teknisi->id_user ? 'selected' : '' }}>
                        {{ $teknisi->nama }}
                    </option>
                    @endforeach
                </select>
                @error('id_user')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Teknisi Tambahan</label>
                <select name="teknisi_ids[]" class="select2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" multiple>
                    @foreach($teknisis as $teknisi)
                    <option value="{{ $teknisi->id_user }}">{{ $teknisi->nama }}</option>
                    @endforeach
                </select>
                @error('teknisi_ids')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Material --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Material Yang Digunakan</h3>
        <div id="material-list" class="space-y-3">
            <div class="material-item flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 p-4 dark:border-slate-600">
                <div class="flex-1 min-w-[200px]">
                    <label class="mb-2 block text-xs font-medium text-gray-500">Material</label>
                    <select name="material_ids[]" class="select2-material w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700">
                        <option value="">-- Pilih Material --</option>
                        @foreach($materials as $material)
                        <option value="{{ $material->id_material }}">{{ $material->nama_material }} (Stok: {{ $material->stok }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-24">
                    <label class="mb-2 block text-xs font-medium text-gray-500">Jumlah</label>
                    <input type="number" name="jumlahs[]" min="1" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700">
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="mb-2 block text-xs font-medium text-gray-500">Keterangan</label>
                    <input type="text" name="keterangans[]" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700">
                </div>
                <button type="button" onclick="removeMaterial(this)" class="rounded-lg bg-red-100 p-2 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
        <button type="button" onclick="addMaterial()" class="mt-4 rounded-xl border-2 border-dashed border-purple-300 px-4 py-2 text-sm font-medium text-purple-600 hover:border-purple-400 hover:bg-purple-50 dark:border-purple-600 dark:text-purple-400 dark:hover:bg-purple-900/20">
            + Tambah Material
        </button>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('jaringan.baa.index') }}" class="rounded-xl border border-gray-200 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">Batal</a>
        <button type="submit" class="rounded-xl bg-purple-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700">Simpan</button>
    </div>
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
        $('.select2-material').select2();
    });

    function addMaterial() {
        const html = `
            <div class="material-item flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 p-4 dark:border-slate-600">
                <div class="flex-1 min-w-[200px]">
                    <label class="mb-2 block text-xs font-medium text-gray-500">Material</label>
                    <select name="material_ids[]" class="select2-material w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700">
                        <option value="">-- Pilih Material --</option>
                        @foreach($materials as $material)
                        <option value="{{ $material->id_material }}">{{ $material->nama_material }} (Stok: {{ $material->stok }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-24">
                    <label class="mb-2 block text-xs font-medium text-gray-500">Jumlah</label>
                    <input type="number" name="jumlahs[]" min="1" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700">
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="mb-2 block text-xs font-medium text-gray-500">Keterangan</label>
                    <input type="text" name="keterangans[]" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700">
                </div>
                <button type="button" onclick="removeMaterial(this)" class="rounded-lg bg-red-100 p-2 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        `;
        $('#material-list').append(html);
        $('#material-list .select2-material').last().select2();
    }

    function removeMaterial(btn) {
        if ($('.material-item').length > 1) {
            $(btn).closest('.material-item').remove();
        }
    }
</script>
@endpush
@endsection
