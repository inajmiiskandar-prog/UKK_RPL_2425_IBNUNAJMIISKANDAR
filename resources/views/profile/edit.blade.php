@extends('layouts.dashboard')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-breadcrumb', 'Profil / Pengaturan Akun')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-600 to-primary-500 p-6 text-white shadow-lg sm:p-8">
        <div class="relative z-10 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl bg-white/20 text-2xl font-bold ring-4 ring-white/20">
                    {{ strtoupper(substr($user->nama ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm text-white/75">Selamat datang kembali</p>
                    <h1 class="font-display text-2xl font-bold sm:text-3xl">{{ $user->nama }}</h1>
                    <p class="mt-1 text-sm text-white/80">{{ $user->role ?? 'User' }} &middot; {{ '@' . $user->username }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-start rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium sm:self-center">
                <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                Akun Aktif
            </div>
        </div>
        <div class="absolute -right-10 -top-16 h-48 w-48 rounded-full border-[24px] border-white/10"></div>
        <div class="absolute -bottom-24 right-24 h-48 w-48 rounded-full border-[18px] border-white/10"></div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-8">
        @include('profile.partials.update-profile-information-form')
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-8">
        @include('profile.partials.update-password-form')
    </div>

    <div class="border border-red-200 bg-red-50/60 p-5 shadow-sm dark:border-red-900/50 dark:bg-red-950/20 sm:rounded-2xl sm:p-8">
        @include('profile.partials.delete-user-form')
    </div>
</div>
@endsection
