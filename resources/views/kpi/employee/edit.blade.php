@extends('layouts.dashboard')

@section('title', 'Edit Karyawan')
@section('page-title', 'Edit Data Karyawan')
@section('page-breadcrumb', 'KPI / Data Karyawan / Edit')

@section('content')
<div class="mb-6">
    <a href="{{ route('kpi.employee.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
</div>

<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h2 class="mb-6 font-display text-lg font-semibold text-gray-800 dark:text-white">Edit Data Karyawan</h2>

    @if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
        <strong class="font-bold">Error!</strong>
        <ul class="mt-2 list-inside list-disc">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
        <strong class="font-bold">Gagal!</strong>
        <p class="mt-1">{{ session('error') }}</p>
    </div>
    @endif

    <form action="{{ route('kpi.employee.update', $employee->id_user) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            {{-- Info Karyawan (Read-only) --}}
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-slate-700/50">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Kode Karyawan</label>
                        <p class="font-semibold text-purple-700 dark:text-purple-400">{{ $employee->kode_karyawan ?? $employee->kode_user ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Nama</label>
                        <p class="font-semibold text-gray-800 dark:text-white">{{ $employee->nama }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Email</label>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $employee->email ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Editable Fields --}}
            <div class="grid gap-4 sm:grid-cols-2">
                {{-- NIK --}}
                <div>
                    <label for="nik" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">NIK</label>
                    <input type="text" name="nik" id="nik" value="{{ old('nik', $employee->nik) }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                           placeholder="Nomor Induk Karyawan">
                </div>

                {{-- Role --}}
                <div>
                    <label for="role" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                    <select name="role" id="role"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <option value="ADMIN" {{ $employee->role == 'ADMIN' ? 'selected' : '' }}>Administrator</option>
                        <option value="LEADER" {{ $employee->role == 'LEADER' ? 'selected' : '' }}>Leader</option>
                        <option value="SALES" {{ $employee->role == 'SALES' ? 'selected' : '' }}>Sales</option>
                        <option value="TEKNISI" {{ $employee->role == 'TEKNISI' ? 'selected' : '' }}>Teknisi</option>
                        <option value="LOGISTIK" {{ $employee->role == 'LOGISTIK' ? 'selected' : '' }}>Logistik</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                {{-- Divisi --}}
                <div>
                    <label for="divisi" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Divisi</label>
                    <input type="text" name="divisi" id="divisi" value="{{ old('divisi', $employee->divisi) }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                           placeholder="Contoh: IT, Sales, Teknik">
                </div>

                {{-- Jabatan --}}
                <div>
                    <label for="jabatan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan', $employee->jabatan) }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                           placeholder="Contoh: Staff, Supervisor">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                {{-- Atasan --}}
                <div>
                    <label for="atasan_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Atasan</label>
                    <select name="atasan_id" id="atasan_id"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <option value="">-- Tidak Ada Atasan --</option>
                        @foreach($allUsers as $u)
                        <option value="{{ $u->id_user }}" {{ $employee->atasan_id == $u->id_user ? 'selected' : '' }}>
                            {{ $u->nama }} ({{ $u->role }})
                        </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Pilih atasan langsung karyawan ini</p>
                </div>

                {{-- Tanggal Masuk --}}
                <div>
                    <label for="join_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Masuk</label>
                    <input type="date" name="join_date" id="join_date" value="{{ old('join_date', $employee->join_date) }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
            </div>

            {{-- Alamat --}}
            <div>
                <label for="alamat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                <textarea name="alamat" id="alamat" rows="2"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                          placeholder="Alamat lengkap">{{ old('alamat', $employee->alamat) }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                {{-- No. Telepon --}}
                <div>
                    <label for="no_telp" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">No. Telepon</label>
                    <input type="text" name="no_telp" id="no_telp" value="{{ old('no_telp', $employee->no_telp) }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                           placeholder="08xxxxxxxxxx">
                </div>

                {{-- Status --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <div class="mt-2">
                        @if($employee->status)
                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">Aktif</span>
                        @else
                        <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">Nonaktif</span>
                        @endif
                        <a href="{{ route('kpi.employee.toggle-status', $employee->id_user) }}"
                           onclick="return confirm('Yakin ingin {{ $employee->status ? 'menonaktifkan' : 'mengaktifkan' }} karyawan ini?')"
                           class="ml-3 text-xs text-purple-600 hover:text-purple-800 dark:text-purple-400">
                            {{ $employee->status ? 'Nonaktifkan' : 'Aktifkan' }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Foto --}}
            <div>
                <label for="foto" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Foto</label>
                @if($employee->foto)
                <div class="mb-2 flex items-center gap-3">
                    <img src="{{ Storage::url($employee->foto) }}" alt="Foto {{ $employee->nama }}"
                         class="h-16 w-16 rounded-full object-cover border border-gray-200 dark:border-slate-600">
                    <span class="text-xs text-gray-500">Foto saat ini (kosongkan jika tidak ingin更换)</span>
                </div>
                @endif
                <input type="file" name="foto" id="foto" accept="image/*"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-purple-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-purple-600 hover:file:bg-purple-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:file:bg-purple-900/30 dark:file:text-purple-400">
                <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG. Maksimal 2MB. Kosongkan jika tidak ingin更换 foto.</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('kpi.employee.index') }}" class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600">Batal</a>
            <button type="submit" class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
