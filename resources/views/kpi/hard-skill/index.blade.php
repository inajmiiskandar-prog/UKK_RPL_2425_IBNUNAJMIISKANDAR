@extends('layouts.dashboard')

@section('title', 'Hard Skill KPI')
@section('page-title', 'Hard Skill')
@section('page-breadcrumb', 'KPI / Hard Skill')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-gray-800 dark:text-white">Hard Skill</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola indikator hard skill per divisi/jabatan</p>
    </div>
    <button type="button" onclick="openAddModal()"
            class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah
    </button>
</div>

@if(session('success'))<div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">{{ session('error') }}</div>@endif

<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <form method="GET" class="flex flex-col gap-4 sm:flex-row">
        <div class="flex-1">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode, KPI, divisi, jabatan..." class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>
        <select name="divisi" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            <option value="">Semua Divisi</option>
            @isset($divisiList)
                @foreach($divisiList as $divisi)
                <option value="{{ $divisi }}" {{ request('divisi') == $divisi ? 'selected' : '' }}>{{ $divisi }}</option>
                @endforeach
            @endisset
        </select>
        <button type="submit" class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">Cari</button>
    </form>
</div>

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-slate-700 dark:bg-slate-700/50">
                <tr>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Kode</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Divisi</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Jabatan</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">KPI</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-center">Target</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-center">Weight</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @forelse($hardSkills as $skill)
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700">
                    <td class="px-4 py-3">
                        <span class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                            {{ $skill->kode }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-800 dark:text-white">{{ $skill->divisi ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-800 dark:text-white">{{ $skill->jabatan ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-800 dark:text-white">{{ $skill->kpi ?? '-' }}</td>
                    <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">{{ $skill->target ?? '100%' }}</td>
                    <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">{{ $skill->weight ? $skill->weight . '%' : '-' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <button type="button" onclick="openEditModal({{ $skill->id }})"
                                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-slate-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form action="{{ route('kpi.hard-skill.destroy', $skill->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Yakin hapus?')"
                                        class="rounded-lg p-2 text-gray-500 hover:bg-red-50 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-900/20">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center"><p class="text-gray-500 dark:text-gray-400">Belum ada data hard skill</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($hardSkills->hasPages())<div class="border-t border-gray-100 px-6 py-4 dark:border-slate-700">{{ $hardSkills->withQueryString()->links() }}</div>@endif
</div>

{{-- Modal Tambah Hard Skill --}}
<div id="modal-add" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeAddModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl dark:bg-slate-800">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-slate-700">
                <h3 class="font-display text-lg font-semibold text-gray-800 dark:text-white">Tambah Hard Skill</h3>
                <button type="button" onclick="closeAddModal()" class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('kpi.hard-skill.store') }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-4">
                    {{-- Divisi & Jabatan 2 kolom --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="add_divisi" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Divisi <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="divisi" id="add_divisi" value="{{ old('divisi') }}" required
                                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                                   placeholder="Contoh: IT, Finance, Marketing">
                        </div>
                        <div>
                            <label for="add_jabatan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Jabatan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="jabatan" id="add_jabatan" value="{{ old('jabatan') }}" required
                                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                                   placeholder="Contoh: Staff, Supervisor, Manager">
                        </div>
                    </div>
                    {{-- Responsibilities --}}
                    <div>
                        <label for="add_responsibilities" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Responsibilities <span class="text-red-500">*</span>
                        </label>
                        <textarea name="responsibilities" id="add_responsibilities" rows="3" required
                                  class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                                  placeholder="Deskripsi tanggung jawab pekerjaan...">{{ old('responsibilities') }}</textarea>
                    </div>
                    {{-- KPI --}}
                    <div>
                        <label for="add_kpi" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            KPI (Nama Indikator) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="kpi" id="add_kpi" value="{{ old('kpi') }}" required
                               class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                               placeholder="Contoh: Penguasaan SQL Database">
                    </div>
                    {{-- Target & Weight 2 kolom --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="add_target" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Target
                            </label>
                            <input type="text" name="target" id="add_target" value="{{ old('target', '100%') }}"
                                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                                   placeholder="100%">
                        </div>
                        <div>
                            <label for="add_weight" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Weight (%)
                            </label>
                            <input type="number" name="weight" id="add_weight" value="{{ old('weight', 0) }}" min="0" max="100" step="0.01"
                                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                                   placeholder="20">
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeAddModal()"
                            class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit"
                            class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">
                        Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Hard Skill --}}
<div id="modal-edit" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeEditModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl dark:bg-slate-800">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-slate-700">
                <h3 class="font-display text-lg font-semibold text-gray-800 dark:text-white">Edit Hard Skill</h3>
                <button type="button" onclick="closeEditModal()" class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="edit-form" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    {{-- Divisi & Jabatan 2 kolom --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="edit_divisi" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Divisi <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="divisi" id="edit_divisi" value="" required
                                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        </div>
                        <div>
                            <label for="edit_jabatan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Jabatan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="jabatan" id="edit_jabatan" value="" required
                                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        </div>
                    </div>
                    {{-- Responsibilities --}}
                    <div>
                        <label for="edit_responsibilities" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Responsibilities <span class="text-red-500">*</span>
                        </label>
                        <textarea name="responsibilities" id="edit_responsibilities" rows="3" required
                                  class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"></textarea>
                    </div>
                    {{-- KPI --}}
                    <div>
                        <label for="edit_kpi" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            KPI (Nama Indikator) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="kpi" id="edit_kpi" value="" required
                               class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    </div>
                    {{-- Target & Weight 2 kolom --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="edit_target" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Target
                            </label>
                            <input type="text" name="target" id="edit_target" value=""
                                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        </div>
                        <div>
                            <label for="edit_weight" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Weight (%)
                            </label>
                            <input type="number" name="weight" id="edit_weight" value="" min="0" max="100" step="0.01"
                                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()"
                            class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit"
                            class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Data untuk modal edit (disimpan di JSON) --}}
<script type="application/json" id="hard-skills-data">
    {!! $hardSkills->mapWithKeys(fn($s) => [$s->id => [
        'divisi' => $s->divisi,
        'jabatan' => $s->jabatan,
        'responsibilities' => $s->responsibilities,
        'kpi' => $s->kpi,
        'target' => $s->target,
        'weight' => $s->weight,
    ]])->toJson() !!}
</script>

<script>
    const hardSkillsData = JSON.parse(document.getElementById('hard-skills-data').textContent);

    function openAddModal() {
        document.getElementById('modal-add').classList.remove('hidden');
        document.getElementById('add_divisi').focus();
    }

    function closeAddModal() {
        document.getElementById('modal-add').classList.add('hidden');
    }

    function openEditModal(id) {
        const data = hardSkillsData[id];
        if (!data) return;

        document.getElementById('edit-form').action = '/kpi/hard-skill/' + id;
        document.getElementById('edit_divisi').value = data.divisi || '';
        document.getElementById('edit_jabatan').value = data.jabatan || '';
        document.getElementById('edit_responsibilities').value = data.responsibilities || '';
        document.getElementById('edit_kpi').value = data.kpi || '';
        document.getElementById('edit_target').value = data.target || '';
        document.getElementById('edit_weight').value = data.weight || '';

        document.getElementById('modal-edit').classList.remove('hidden');
        document.getElementById('edit_divisi').focus();
    }

    function closeEditModal() {
        document.getElementById('modal-edit').classList.add('hidden');
    }

    // Tutup modal dengan Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddModal();
            closeEditModal();
        }
    });
</script>
@endsection
