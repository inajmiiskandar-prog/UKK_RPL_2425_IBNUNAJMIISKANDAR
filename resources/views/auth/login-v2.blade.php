@extends('layouts.guest')

@section('content')
<style>
    .font-display { font-family: 'Sora', ui-sans-serif, sans-serif; }
    .font-body { font-family: 'Inter', ui-sans-serif, sans-serif; }

    /* Gradient background animation */
    .animated-bg {
        background: linear-gradient(-45deg, #f8fafc, #f1f5f9, #e2e8f0, #cbd5e1);
        background-size: 400% 400%;
        animation: gradient 15s ease infinite;
    }
    @keyframes gradient {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    /* Card hover effect */
    .login-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .login-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }

    /* Input focus ring animation */
    .input-field {
        transition: all 0.3s ease;
    }
    .input-field:focus {
        box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.2);
    }

    /* Button shine effect */
    .btn-login {
        position: relative;
        overflow: hidden;
    }
    .btn-login::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s ease;
    }
    .btn-login:hover::before {
        left: 100%;
    }

    /* Password toggle button */
    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        width: 2rem;
        height: 2rem;
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.85);
        cursor: pointer;
        color: #64748b;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .password-toggle:hover {
        color: #7c3aed;
        border-color: rgba(124, 58, 237, 0.45);
        background: rgba(250, 245, 255, 1);
    }
    .password-toggle svg {
        width: 1.1rem;
        height: 1.1rem;
    }

    /* Mobile adjustments */
    @media (max-width: 767px) {
        .login-logo {
            height: 72px !important;
            width: 72px !important;
        }
        .login-card {
            padding: 1.5rem;
            border-radius: 1.5rem;
        }
        .form-input {
            height: 52px;
            font-size: 16px;
        }
        .btn-login {
            height: 52px;
        }
    }

    /* Desktop spacing */
    @media (min-width: 1024px) {
        .section-gap {
            gap: 80px !important;
        }
    }
    @media (min-width: 1280px) {
        .section-gap {
            gap: 100px !important;
        }
    }
</style>

<div class="animated-bg flex min-h-screen font-body">

    {{-- ================================== --}}
    {{-- KIRI: Branding & Info              --}}
    {{-- ================================== --}}
    <div class="hidden w-1/2 flex-col justify-center bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 p-12 text-white lg:flex">
        <div class="max-w-md">
            <img
                src="{{ asset('images/logo-passnet.png') }}"
                alt="Passnet"
                class="mb-8 h-24 w-24 rounded-2xl bg-white/10 p-2 shadow-xl backdrop-blur"
            >

            <h1 class="font-display mb-4 text-4xl font-bold leading-tight">
                PT PASS INTERNET INDONESIA
            </h1>

            <p class="mb-8 text-lg italic text-white/80">
                &ldquo;Connecting the Future&rdquo;
            </p>

            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 backdrop-blur">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-white/90">Keamanan Terjamin</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 backdrop-blur">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                        </svg>
                    </div>
                    <span class="text-white/90">Akses Mudah & Cepat</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 backdrop-blur">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-white/90">Solusi Internet Terpercaya</span>
                </div>
            </div>
        </div>

        <p class="mt-auto text-sm text-white/60">
            &copy; {{ date('Y') }} PT Pass Internet Indonesia. All rights reserved.
        </p>
    </div>

    {{-- ================================== --}}
    {{-- KANAN: Form Login                  --}}
    {{-- ================================== --}}
    <div class="flex w-full items-center justify-center px-6 py-8 lg:w-1/2">
        <div class="w-full max-w-md section-gap flex flex-col items-center">

            {{-- Mobile Logo --}}
            <div class="mb-6 lg:hidden">
                <img
                    src="{{ asset('images/logo-passnet.png') }}"
                    alt="Passnet"
                    class="login-logo h-20 w-20 rounded-full bg-white p-2 shadow-lg"
                >
            </div>

            <div class="login-card w-full rounded-3xl border border-gray-100 bg-white p-8 shadow-xl">
                <div class="mb-6 text-center lg:text-left">
                    <p class="font-display text-xs font-semibold uppercase tracking-wider text-violet-600">
                        Selamat Datang
                    </p>
                    <h1 class="font-display mt-2 text-2xl font-bold text-gray-900">
                        Masuk ke Passnet
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ config('app.name') }}
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-red-100 bg-red-50 p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-red-600">{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="username" class="mb-2 block text-sm font-medium text-gray-700">
                            Username
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            <input
                                id="username"
                                type="text"
                                name="username"
                                value="{{ old('username') }}"
                                placeholder="Masukkan username Anda"
                                class="input-field form-input h-12 w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-12 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-violet-500 focus:bg-white focus:outline-none"
                                required
                                autofocus
                                autocomplete="username"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Masukkan password Anda"
                                class="input-field form-input h-12 w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-12 pr-12 text-sm text-gray-800 placeholder:text-gray-400 focus:border-violet-500 focus:bg-white focus:outline-none"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" onclick="togglePassword()" class="password-toggle" aria-label="Toggle password" title="Lihat/Sembunyikan password">
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>
                                    <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                                <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 111.06 1.06l-2.5 2.5a.75.75 0 01-1.06-1.06l2.5-2.5zm14.44 14.44a.75.75 0 011.06 1.06l-2.5 2.5a.75.75 0 11-1.06-1.06l2.5-2.5zM10 5a5 5 0 015 5c0 .65-.12 1.27-.35 1.84l1.68 1.68A6.98 6.98 0 0017 10a7.01 7.01 0 00-7-7c-1.44 0-2.8.43-3.95 1.17l1.64 1.64A5.04 5.04 0 0110 5zm0 10a5 5 0 01-5-5c0-.65.12-1.27.35-1.84L3.67 6.48A6.98 6.98 0 003 10a7.01 7.01 0 007 7c1.44 0 2.8-.43 3.95-1.17l-1.64-1.64A5.04 5.04 0 0110 15zm-2.5-2.5l-1.5-1.5a.75.75 0 011.06-1.06l1.5 1.5 1.5-1.5a.75.75 0 011.06 1.06l-1.5 1.5 1.5 1.5a.75.75 0 11-1.06 1.06l-1.5-1.5-1.5 1.5a.75.75 0 11-1.06-1.06z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn-login flex h-12 w-full items-center justify-center rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 font-display text-sm font-semibold text-white shadow-lg transition-all hover:shadow-xl active:scale-[0.98]"
                    >
                        <span>Masuk</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            <path fill-rule="evenodd" d="M9.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-gray-400 lg:hidden">
                &copy; {{ date('Y') }} PT Pass Internet Indonesia
            </p>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeOffIcon = document.getElementById('eye-off-icon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeOffIcon.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeOffIcon.classList.add('hidden');
        }
    }
</script>
@endsection
