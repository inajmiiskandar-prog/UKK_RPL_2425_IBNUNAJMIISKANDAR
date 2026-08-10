@extends('layouts.guest')

@section('content')
<style>
    .font-display { font-family: 'Sora', ui-sans-serif, sans-serif; }
    .font-body { font-family: 'Inter', ui-sans-serif, sans-serif; }

    .fade-up {
        opacity: 0;
        transform: translateY(10px);
        animation: fade-up .7s ease-out forwards;
    }
    .fade-up.d1 { animation-delay: .1s; }
    .fade-up.d2 { animation-delay: .25s; }
    .fade-up.d3 { animation-delay: .4s; }
    .fade-up.d4 { animation-delay: .55s; }
    .fade-up.d5 { animation-delay: .7s; }

    @keyframes fade-up {
        to { opacity: 1; transform: translateY(0); }
    }

    /* Float animation for logo */
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
    }

    .float-logo {
        animation: float 3s ease-in-out infinite;
    }

    /* Pulse glow animation */
    @keyframes pulse-glow {
        0%, 100% { opacity: 0.5; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.1); }
    }

    .pulse-glow {
        animation: pulse-glow 2s ease-in-out infinite;
    }

    @media (prefers-reduced-motion: reduce) {
        .fade-up { animation: none; opacity: 1; transform: none; }
    }

    /* Mobile-only adjustments (only apply on small screens) */
    @media (max-width: 767px) {
        .login-container {
            padding-left: 1rem;
            padding-right: 1rem;
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }

        .login-logo {
            height: 80px !important;
            width: 80px !important;
        }

        .login-section-gap {
            gap: 1.5rem;
        }

        /* Form stays readable but compact on mobile */
        .login-card {
            padding: 1.25rem;
            border-radius: 1.25rem;
        }

        .login-title {
            font-size: 1.125rem;
        }

        .form-input {
            height: 48px;
            font-size: 16px; /* Prevents zoom on iOS */
        }

        .form-button {
            height: 48px;
        }
    }

    /* Desktop large screens - more spacing */
    @media (min-width: 1024px) {
        .login-section-gap {
            gap: 100px !important;
        }
    }

    @media (min-width: 1280px) {
        .login-section-gap {
            gap: 140px !important;
        }
    }

    /* Password toggle button */
    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #94a3b8;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }
    .password-toggle:hover {
        color: #64748b;
    }
</style>

<div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-50 px-4 py-8 font-body sm:px-6 md:py-12 login-container">

    {{-- glow lembut di latar --}}
    <div class="pointer-events-none absolute -top-40 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-fuchsia-400/20 blur-3xl sm:h-96 sm:w-96"></div>
    <div class="pointer-events-none absolute -bottom-40 -right-20 h-72 w-72 rounded-full bg-sky-400/20 blur-3xl sm:h-96 sm:w-96"></div>
    <div class="pointer-events-none absolute -bottom-40 -left-20 h-72 w-72 rounded-full bg-purple-400/20 blur-3xl sm:h-96 sm:w-96"></div>

    <div class="relative flex w-full max-w-5xl flex-col items-center gap-8 md:flex-row md:items-center md:justify-between login-section-gap">

        {{-- ================================== --}}
        {{-- KIRI: Logo besar + nama perusahaan  --}}
        {{-- ================================== --}}
        <div class="fade-up d1 flex flex-1 flex-col items-center text-center">

            <div class="relative mb-4 sm:mb-6">
                {{-- cahaya berdenyut di belakang logo --}}
                <div class="pulse-glow pointer-events-none absolute inset-0 -z-10 rounded-full bg-fuchsia-400/30 blur-2xl"></div>

                <img
                    src="{{ asset('images/logo-passnet.png') }}"
                    alt="Passnet"
                    class="float-logo h-24 w-24 rounded-full shadow-xl shadow-fuchsia-500/25 sm:h-36 sm:w-36 login-logo md:h-44 md:w-44"
                >
            </div>

            <p class="font-display text-lg font-bold tracking-wide text-slate-800 sm:text-2xl md:text-3xl">
                PT PASS INTERNET INDONESIA
            </p>
            <p class="mt-1 text-xs italic text-fuchsia-600 sm:mt-2 sm:text-base md:text-lg">
                &ldquo;connecting the future&rdquo;
            </p>
        </div>

        {{-- ================================== --}}
        {{-- KANAN: Card form login              --}}
        {{-- ================================== --}}
        <div class="w-full max-w-md flex-1">

            <div class="fade-up d4 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl sm:p-8 login-card">
                <p class="font-display text-xs font-semibold uppercase tracking-widest text-fuchsia-600">
                    Selamat datang kembali
                </p>
                <h1 class="font-display mt-2 text-xl font-bold text-slate-900 sm:text-2xl login-title">
                    Masuk ke akun Anda
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ config('app.name') }}
                </p>

                @if ($errors->any())
                    <div class="mt-6 flex items-start gap-2 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">
                            Username
                        </label>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-4 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3.75 3.75 0 100-7.5A3.75 3.75 0 0010 9zm-7 8a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            <input
                                id="username"
                                type="text"
                                name="username"
                                value="{{ old('username') }}"
                                placeholder="Masukkan username"
                                class="form-input h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm text-slate-800 transition-all placeholder:text-slate-400 focus:border-fuchsia-400 focus:bg-white focus:ring-4 focus:ring-fuchsia-500/10 focus:outline-none sm:pl-11"
                                required
                                autofocus
                                autocomplete="username"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">
                            Password
                        </label>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-4 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                            </svg>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Masukkan password"
                                class="form-input h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-12 text-sm text-slate-800 transition-all placeholder:text-slate-400 focus:border-fuchsia-400 focus:bg-white focus:ring-4 focus:ring-fuchsia-500/10 focus:outline-none sm:pr-4"
                                required
                                autocomplete="current-password"
                            >
                            <button
                                type="button"
                                onclick="togglePassword()"
                                class="password-toggle"
                                aria-label="Toggle password visibility"
                            >
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>
                                    <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                                <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9.77 2.23a.75.75 0 011.06 1.06L8.27 6.85l1.47 1.47a.75.75 0 01-1.06 1.06l-1.47-1.47-1.47 1.47a.75.75 0 11-1.06-1.06l1.47-1.47-1.47-1.47a.75.75 0 111.06-1.06l1.47 1.47 1.47-1.47a.75.75 0 011.06 1.06l-1.47 1.47 1.47 1.47a.75.75 0 11-1.06 1.06l-1.47-1.47-1.47 1.47z" clip-rule="evenodd"/>
                                    <path fill-rule="evenodd" d="M14 10a6 6 0 01-5.23 5.96l-.7-.7A8.002 8.002 0 0110 17a8 8 0 008-5.4l-1.77 1.77A6 6 0 0114 10zm-4-3a4 4 0 100 8 4 4 0 000-8z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        style="cursor: pointer;"
                        class="form-button flex h-12 w-full items-center justify-center rounded-2xl bg-gradient-to-r from-purple-600 via-fuchsia-500 to-sky-500 font-display text-sm font-semibold text-white shadow-lg shadow-fuchsia-500/25 transition-transform hover:scale-[1.01] active:scale-[0.99]"
                    >
                        Masuk
                    </button>
                </form>
            </div>

            <p class="fade-up d5 mt-6 text-center text-xs text-slate-400">
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
