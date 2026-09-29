@extends('layouts.dashboard')

@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('page-breadcrumb', 'Users / Edit')

@section('content')
<div class="mb-6">
    <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h2 class="mb-6 font-display text-lg font-semibold text-gray-800 dark:text-white">Form Edit User</h2>

    @if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">Terjadi kesalahan:</p>
        <ul class="mt-1 list-inside list-disc text-sm text-red-600 dark:text-red-300">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('users.update', $user->id_user) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="kode_user" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Kode User</label>
                <input type="text" id="kode_user" value="{{ $user->kode_user }}" disabled class="w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 py-3 text-sm text-gray-500 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-400">
            </div>
            <div>
                <label for="nama" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" id="nama" name="nama" value="{{ old('nama', $user->nama) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="username" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Username <span class="text-red-500">*</span></label>
                <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
            </div>
            <div>
                <label for="role" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Role <span class="text-red-500">*</span></label>
                <select id="role" name="role" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="ADMIN" {{ $user->role == 'ADMIN' ? 'selected' : '' }}>Admin</option>
                    <option value="LEADER" {{ $user->role == 'LEADER' ? 'selected' : '' }}>Leader</option>
                    <option value="SALES" {{ $user->role == 'SALES' ? 'selected' : '' }}>Sales</option>
                    <option value="TEKNISI" {{ $user->role == 'TEKNISI' ? 'selected' : '' }}>Teknisi</option>
                    <option value="LOGISTIK" {{ $user->role == 'LOGISTIK' ? 'selected' : '' }}>Logistik</option>
                </select>
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Password Baru</label>
                <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
            <div>
                <label for="jkl" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Jenis Kelamin <span class="text-red-500">*</span></label>
                <select id="jkl" name="jkl" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                    <option value="LAKI_LAKI" {{ $user->jkl == 'LAKI_LAKI' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="PEREMPUAN" {{ $user->jkl == 'PEREMPUAN' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="no_hp" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">No. HP</label>
                <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="foto" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Foto (ubah)</label>
                <input type="file" id="foto" name="foto" accept="image/*" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-purple-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-purple-600 hover:file:bg-purple-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:file:bg-purple-900/30 dark:file:text-purple-400">
                @error('foto')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                @if($user->foto)
                <img src="{{ Storage::url($user->foto) }}" alt="Foto {{ $user->nama }}" class="h-12 w-12 rounded-full object-cover border border-gray-200">
                @endif
            </div>
        </div>
        <div>
            <label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white" required>
                <option value="1" {{ $user->status ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ !$user->status ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <div class="flex items-center gap-3 pt-4">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700 hover:shadow-xl">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Update
            </button>
            <a href="{{ route('users.index') }}" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">Batal</a>
        </div>
    </form>
</div>
@endsection
