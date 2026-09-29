@extends('layouts.dashboard')

@section('title', 'Material')
@section('page-title', 'Material')
@section('page-breadcrumb', 'Master Data / Material')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-gray-800 dark:text-white">Data Material</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola material instalasi</p>
    </div>
    <a href="{{ route('masterdata.material.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Material
    </a>
</div>

@if(session('success'))<div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">{{ session('success') }}</div>@endif

<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <form method="GET" class="flex flex-col gap-4 sm:flex-row">
        <div class="flex-1">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari material..." class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>
        <select name="kondisi" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            <option value="">Semua Kondisi</option>
            <option value="BAIK" {{ request('kondisi') == 'BAIK' ? 'selected' : '' }}>Baik</option>
            <option value="RUSAK" {{ request('kondisi') == 'RUSAK' ? 'selected' : '' }}>Rusak</option>
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
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Nama Material</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Stok</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Harga</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Kondisi</th>
                    <th class="px-6 py-4 text-right font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @forelse($materials as $material)
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700">
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-semibold text-teal-700 dark:bg-teal-900/30 dark:text-teal-400">{{ $material->kode_material }}</span></td>
                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">{{ $material->nama_material }}</td>
                    <td class="px-6 py-4">
                        <span class="font-semibold {{ $material->stok <= $material->minimal_stok ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-white' }}">
                            {{ $material->stok }} {{ $material->satuan }}
                        </span>
                        @if($material->stok <= $material->minimal_stok)
                        <span class="ml-1 text-xs text-red-500">(Habis)</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">Rp {{ number_format($material->harga, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $material->kondisi == 'BAIK' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                            {{ $material->kondisi }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('masterdata.material.show', $material->id_material) }}" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-purple-600 dark:text-gray-400 dark:hover:bg-slate-700"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                            <button type="button" onclick="openStockModal('{{ $material->id_material }}', '{{ $material->nama_material }}', {{ $material->stok }}, '{{ $material->satuan }}')" class="rounded-lg p-2 text-gray-500 hover:bg-green-100 hover:text-green-600 dark:text-gray-400 dark:hover:bg-green-900/20 dark:hover:text-green-400" title="Tambah Stok">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </button>
                            <a href="{{ route('masterdata.material.edit', $material->id_material) }}" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-slate-700"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <form action="{{ route('masterdata.material.destroy', $material->id_material) }}" method="POST" class="inline">@csrf @method('DELETE')<button type="submit" onclick="return confirm('Yakin hapus material {{ $material->nama_material }}?')" class="rounded-lg p-2 text-gray-500 hover:bg-red-50 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-900/20 dark:hover:text-red-400"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center"><p class="text-gray-500 dark:text-gray-400">Belum ada data material</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($materials->hasPages())<div class="border-t border-gray-100 px-6 py-4 dark:border-slate-700">{{ $materials->withQueryString()->links() }}</div>@endif
</div>

{{-- Modal Tambah Stok --}}
<div id="stockModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeStockModal()"></div>
    <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2">
        <div class="rounded-xl bg-white p-6 shadow-xl dark:bg-slate-800">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Tambah Stok Material</h3>
                <button onclick="closeStockModal()" class="rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-slate-700">
                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p id="modalMaterialName" class="mb-4 text-sm text-gray-600 dark:text-gray-400"></p>
            <p id="modalCurrentStock" class="mb-4 text-sm text-gray-600 dark:text-gray-400"></p>
            <form id="stockForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah Tambah</label>
                    <input type="number" name="jumlah_tambah" id="jumlahTambah" min="1" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" placeholder="Masukkan jumlah">
                </div>
                <p id="modalNewStock" class="mb-4 text-sm font-medium text-gray-700 dark:text-gray-300"></p>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeStockModal()" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">Batal</button>
                    <button type="submit" class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openStockModal(id, nama, stok, satuan) {
    document.getElementById('stockForm').action = '/masterdata/material/' + id + '/add-stock';
    document.getElementById('modalMaterialName').textContent = 'Material: ' + nama;
    document.getElementById('modalCurrentStock').textContent = 'Stok Saat Ini: ' + stok + ' ' + satuan;
    document.getElementById('modalNewStock').textContent = 'Stok Baru: ' + stok + ' + (jumlah) = ?';
    document.getElementById('jumlahTambah').value = '';
    document.getElementById('stockModal').classList.remove('hidden');
}

function closeStockModal() {
    document.getElementById('stockModal').classList.add('hidden');
}

document.getElementById('jumlahTambah').addEventListener('input', function() {
    const stok = parseInt(document.getElementById('modalCurrentStock').textContent.match(/\d+/)[0]);
    const jumlah = parseInt(this.value) || 0;
    document.getElementById('modalNewStock').textContent = 'Stok Baru: ' + stok + ' + ' + jumlah + ' = ' + (stok + jumlah);
});
</script>

@endsection
