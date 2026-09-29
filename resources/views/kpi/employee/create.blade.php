@extends('layouts.dashboard')

@section('title', 'Tambah Karyawan')
@section('page-title', 'Tambah Karyawan')
@section('page-breadcrumb', 'KPI / Data Karyawan / Tambah')

@section('content')
<div class="mb-6">
    <a href="{{ route('kpi.employee.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
</div>

<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h2 class="mb-6 font-display text-lg font-semibold text-gray-800 dark:text-white">Tambah Karyawan Baru</h2>

    @if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400" style="display:block !important;">
        <strong class="font-bold">Error!</strong>
        <ul class="mt-2 list-inside list-disc">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Session Error (dari catch exception) --}}
    @if(session('error'))
    <div class="mb-4 rounded-xl border border-red-200 bg-red-100 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/40 dark:text-red-300" style="display:block !important; background-color: #fee2e2;">
        <strong class="font-bold">Gagal!</strong>
        <p class="mt-1">{{ session('error') }}</p>
    </div>
    @endif

    <form action="{{ route('kpi.employee.store') }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                {{-- Nama --}}
                <div>
                    <label for="nama" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required
                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                           placeholder="Nama lengkap">
                </div>

                {{-- NIK --}}
                <div>
                    <label for="nik" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        NIK
                    </label>
                    <input type="text" name="nik" id="nik" value="{{ old('nik') }}"
                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                           placeholder="Nomor Induk Karyawan">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                {{-- Divisi --}}
                <div>
                    <label for="divisi" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Divisi
                    </label>
                    <input type="text" name="divisi" id="divisi" value="{{ old('divisi') }}"
                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                           placeholder="Contoh: IT, Sales, Teknik">
                </div>

                {{-- Jabatan --}}
                <div>
                    <label for="jabatan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jabatan
                    </label>
                    <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan') }}"
                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                           placeholder="Contoh: Staff, Supervisor">
                </div>
            </div>

            {{-- Alamat --}}
            <div>
                <label for="alamat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Alamat
                </label>
                <textarea name="alamat" id="alamat" rows="2"
                          class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                          placeholder="Alamat lengkap">{{ old('alamat') }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                {{-- Telepon --}}
                <div>
                    <label for="telepon" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Telepon
                    </label>
                    <input type="text" name="telepon" id="telepon" value="{{ old('telepon') }}"
                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                           placeholder="08xxxxxxxxxx">
                </div>

                {{-- Tanggal Masuk --}}
                <div>
                    <label for="tanggal_masuk" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tanggal Masuk
                    </label>
                    <input type="date" name="tanggal_masuk" id="tanggal_masuk" value="{{ old('tanggal_masuk') }}"
                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                {{-- Role --}}
                <div>
                    <label for="role" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select name="role" id="role" required
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <option value="">-- Pilih Role --</option>
                        <option value="KARYAWAN" {{ old('role') === 'KARYAWAN' ? 'selected' : '' }}>Karyawan</option>
                        <option value="ATASAN" {{ old('role') === 'ATASAN' ? 'selected' : '' }}>Atasan</option>
                        <option value="HR" {{ old('role') === 'HR' ? 'selected' : '' }}>HR</option>
                        <option value="ADMIN" {{ old('role') === 'ADMIN' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 pr-10 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                               placeholder="Minimal 6 karakter">
                        <button type="button" onclick="togglePassword('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg id="eye-password" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('kpi.employee.index') }}" class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600">Batal</a>
            <button type="submit" class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">Simpan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('svg');
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
    }
}
</script>
@endpush
