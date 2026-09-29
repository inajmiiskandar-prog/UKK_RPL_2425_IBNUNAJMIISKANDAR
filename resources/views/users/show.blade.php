@extends('layouts.dashboard')

@section('title', 'Detail User')
@section('page-title', 'Detail User')
@section('page-breadcrumb', 'Users / Detail')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
    <a href="{{ route('users.edit', $user->id_user) }}" class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
</div>

<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="mb-6 flex items-center gap-4">
        @if($user->foto)
        <img src="{{ Storage::url($user->foto) }}" alt="Foto {{ $user->nama }}" class="h-16 w-16 cursor-pointer rounded-full object-cover" onclick="openUserPhoto(this)">
        @else
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-pink-500 text-white text-2xl font-bold">
            {{ substr($user->nama, 0, 1) }}
        </div>
        @endif
        <div>
            <h2 class="font-display text-xl font-bold text-gray-800 dark:text-white">{{ $user->nama }}</h2>
            <span class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold @if($user->role == 'ADMIN') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 @elseif($user->role == 'LEADER') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 @elseif($user->role == 'SALES') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 @elseif($user->role == 'TEKNISI') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 @else bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 @endif">
                {{ $user->role }}
            </span>
            <span class="ml-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $user->status ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                {{ $user->status ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>
    </div>
    <div class="grid gap-4 md:grid-cols-2">
        <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Kode User</p><p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">{{ $user->kode_user }}</p></div>
        <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Username</p><p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">{{ $user->username }}</p></div>
        <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Email</p><p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $user->email ?? '-' }}</p></div>
        <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">No. HP</p><p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $user->no_hp ?? '-' }}</p></div>
        <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Jenis Kelamin</p><p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $user->jkl == 'LAKI_LAKI' ? 'Laki-laki' : 'Perempuan' }}</p></div>
        <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Tanggal Dibuat</p><p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $user->createdAt ? \Carbon\Carbon::parse($user->createdAt)->format('d M Y') : '-' }}</p></div>
    </div>
</div>
<div id="userPhotoModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/80 p-4" onclick="closeUserPhoto()">
    <button type="button" class="absolute right-4 top-4 rounded-full bg-white/15 p-2 text-white transition hover:bg-white/30" aria-label="Tutup foto">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <img id="userPhotoPreview" src="" alt="Foto user" class="max-h-[90vh] max-w-full rounded-2xl object-contain shadow-2xl" onclick="event.stopPropagation()">
</div>

<script>
function openUserPhoto(image) {
    document.getElementById('userPhotoPreview').src = image.src;
    document.getElementById('userPhotoModal').classList.remove('hidden');
    document.getElementById('userPhotoModal').classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeUserPhoto() {
    document.getElementById('userPhotoModal').classList.add('hidden');
    document.getElementById('userPhotoModal').classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closeUserPhoto();
});
</script>
@endsection
