@extends('layouts.dashboard')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')
@section('page-breadcrumb', 'Pengaturan')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/nano.min.css" />
<style>
    .pickr { width: 100% !important; }
</style>
@endpush

@section('content')
<div class="mb-6">
    <h1 class="font-display text-2xl font-bold text-gray-800 dark:text-white">Pengaturan Aplikasi</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400">Kelola pengaturan aplikasi dan tampilan</p>
</div>

@if(session('success'))
<div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
    {{ session('error') }}
</div>
@endif

<form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Logo & Identitas --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-4 flex items-center gap-2 font-semibold text-gray-800 dark:text-white">
                <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Logo & Identitas
            </h3>
            <div class="space-y-4">
                {{-- Preview --}}
                <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-slate-700 dark:bg-slate-700/50">
                    <div class="flex-shrink-0">
                        <img id="logo-preview" src="{{ asset($settings['logo_path'] ?? 'images/logo-passnet.png') }}" alt="Logo" class="rounded-lg object-cover" style="width: {{ $settings['logo_width'] ?? 40 }}px; height: {{ $settings['logo_height'] ?? 40 }}px;">
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $settings['app_name'] ?? 'Passnet' }}</p>
                        <p class="text-xs text-gray-500">{{ $settings['app_tagline'] ?? 'ISP Management' }}</p>
                    </div>
                </div>

                {{-- App Name --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Aplikasi</label>
                    <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? 'Passnet') }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>

                {{-- Tagline --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tagline</label>
                    <input type="text" name="app_tagline" value="{{ old('app_tagline', $settings['app_tagline'] ?? 'ISP Management') }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>

                {{-- Logo Upload --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Logo Baru</label>
                    <input type="file" name="logo" accept="image/*" id="logo-input" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-purple-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-purple-700 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500">Format: PNG, JPG, GIF. Maks 2MB.</p>
                </div>

                {{-- Logo Dimensions --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Lebar Logo (px)</label>
                        <input type="number" name="logo_width" value="{{ old('logo_width', $settings['logo_width'] ?? 40) }}" min="20" max="200" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tinggi Logo (px)</label>
                        <input type="number" name="logo_height" value="{{ old('logo_height', $settings['logo_height'] ?? 40) }}" min="20" max="200" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    </div>
                </div>
            </div>
        </div>

        {{-- Font & Typography --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-4 flex items-center gap-2 font-semibold text-gray-800 dark:text-white">
                <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                Font & Tipografi
            </h3>
            <div class="space-y-4">
                {{-- Display Font --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Font Judul (Display)</label>
                    <select name="font_family_display" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <option value="Sora" {{ ($settings['font_family_display'] ?? 'Sora') == 'Sora' ? 'selected' : '' }}>Sora</option>
                        <option value="Poppins" {{ ($settings['font_family_display'] ?? 'Sora') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                        <option value="Montserrat" {{ ($settings['font_family_display'] ?? 'Sora') == 'Montserrat' ? 'selected' : '' }}>Montserrat</option>
                        <option value="Playfair Display" {{ ($settings['font_family_display'] ?? 'Sora') == 'Playfair Display' ? 'selected' : '' }}>Playfair Display</option>
                        <option value="Bebas Neue" {{ ($settings['font_family_display'] ?? 'Sora') == 'Bebas Neue' ? 'selected' : '' }}>Bebas Neue</option>
                        <option value="Oswald" {{ ($settings['font_family_display'] ?? 'Sora') == 'Oswald' ? 'selected' : '' }}>Oswald</option>
                        <option value="Raleway" {{ ($settings['font_family_display'] ?? 'Sora') == 'Raleway' ? 'selected' : '' }}>Raleway</option>
                        <option value="DM Sans" {{ ($settings['font_family_display'] ?? 'Sora') == 'DM Sans' ? 'selected' : '' }}>DM Sans</option>
                        <option value="Manrope" {{ ($settings['font_family_display'] ?? 'Sora') == 'Manrope' ? 'selected' : '' }}>Manrope</option>
                        <option value="Space Grotesk" {{ ($settings['font_family_display'] ?? 'Sora') == 'Space Grotesk' ? 'selected' : '' }}>Space Grotesk</option>
                        <option value="Plus Jakarta Sans" {{ ($settings['font_family_display'] ?? 'Sora') == 'Plus Jakarta Sans' ? 'selected' : '' }}>Plus Jakarta Sans</option>
                        <option value="Barlow" {{ ($settings['font_family_display'] ?? 'Sora') == 'Barlow' ? 'selected' : '' }}>Barlow</option>
                        <option value="Archivo" {{ ($settings['font_family_display'] ?? 'Sora') == 'Archivo' ? 'selected' : '' }}>Archivo</option>
                        <option value="Urbanist" {{ ($settings['font_family_display'] ?? 'Sora') == 'Urbanist' ? 'selected' : '' }}>Urbanist</option>
                        <option value="Outfit" {{ ($settings['font_family_display'] ?? 'Sora') == 'Outfit' ? 'selected' : '' }}>Outfit</option>
                        <option value="Bitter" {{ ($settings['font_family_display'] ?? 'Sora') == 'Bitter' ? 'selected' : '' }}>Bitter</option>
                        <option value="Merriweather" {{ ($settings['font_family_display'] ?? 'Sora') == 'Merriweather' ? 'selected' : '' }}>Merriweather</option>
                        <option value="Libre Baskerville" {{ ($settings['font_family_display'] ?? 'Sora') == 'Libre Baskerville' ? 'selected' : '' }}>Libre Baskerville</option>
                    </select>
                </div>

                {{-- Body Font --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Font Body (Teks)</label>
                    <select name="font_family_body" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <option value="Inter" {{ ($settings['font_family_body'] ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                        <option value="Roboto" {{ ($settings['font_family_body'] ?? 'Inter') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                        <option value="Open Sans" {{ ($settings['font_family_body'] ?? 'Inter') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                        <option value="Lato" {{ ($settings['font_family_body'] ?? 'Inter') == 'Lato' ? 'selected' : '' }}>Lato</option>
                        <option value="Nunito" {{ ($settings['font_family_body'] ?? 'Inter') == 'Nunito' ? 'selected' : '' }}>Nunito</option>
                        <option value="Work Sans" {{ ($settings['font_family_body'] ?? 'Inter') == 'Work Sans' ? 'selected' : '' }}>Work Sans</option>
                        <option value="Quicksand" {{ ($settings['font_family_body'] ?? 'Inter') == 'Quicksand' ? 'selected' : '' }}>Quicksand</option>
                        <option value="DM Sans" {{ ($settings['font_family_body'] ?? 'Inter') == 'DM Sans' ? 'selected' : '' }}>DM Sans</option>
                        <option value="DM Serif Display" {{ ($settings['font_family_body'] ?? 'Inter') == 'DM Serif Display' ? 'selected' : '' }}>DM Serif Display</option>
                        <option value="Nunito Sans" {{ ($settings['font_family_body'] ?? 'Inter') == 'Nunito Sans' ? 'selected' : '' }}>Nunito Sans</option>
                        <option value="Source Sans 3" {{ ($settings['font_family_body'] ?? 'Inter') == 'Source Sans 3' ? 'selected' : '' }}>Source Sans 3</option>
                        <option value="IBM Plex Sans" {{ ($settings['font_family_body'] ?? 'Inter') == 'IBM Plex Sans' ? 'selected' : '' }}>IBM Plex Sans</option>
                        <option value="Mulish" {{ ($settings['font_family_body'] ?? 'Inter') == 'Mulish' ? 'selected' : '' }}>Mulish</option>
                        <option value="Rubik" {{ ($settings['font_family_body'] ?? 'Inter') == 'Rubik' ? 'selected' : '' }}>Rubik</option>
                        <option value="Manrope" {{ ($settings['font_family_body'] ?? 'Inter') == 'Manrope' ? 'selected' : '' }}>Manrope</option>
                        <option value="Plus Jakarta Sans" {{ ($settings['font_family_body'] ?? 'Inter') == 'Plus Jakarta Sans' ? 'selected' : '' }}>Plus Jakarta Sans</option>
                        <option value="Figtree" {{ ($settings['font_family_body'] ?? 'Inter') == 'Figtree' ? 'selected' : '' }}>Figtree</option>
                        <option value="Cabin" {{ ($settings['font_family_body'] ?? 'Inter') == 'Cabin' ? 'selected' : '' }}>Cabin</option>
                        <option value="Barlow" {{ ($settings['font_family_body'] ?? 'Inter') == 'Barlow' ? 'selected' : '' }}>Barlow</option>
                        <option value="Karla" {{ ($settings['font_family_body'] ?? 'Inter') == 'Karla' ? 'selected' : '' }}>Karla</option>
                        <option value="PT Sans" {{ ($settings['font_family_body'] ?? 'Inter') == 'PT Sans' ? 'selected' : '' }}>PT Sans</option>
                        <option value="Source Serif 4" {{ ($settings['font_family_body'] ?? 'Inter') == 'Source Serif 4' ? 'selected' : '' }}>Source Serif 4</option>
                    </select>
                </div>

                {{-- Font Preview --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-slate-700 dark:bg-slate-700/50">
                    <p class="text-xs text-gray-500 mb-2">Preview:</p>
                    <p id="font-display-preview" class="text-xl font-bold" style="font-family: '{{ $settings['font_family_display'] ?? 'Sora' }}', sans-serif;">
                        {{ $settings['app_name'] ?? 'Passnet' }}
                    </p>
                    <p id="font-body-preview" class="text-sm text-gray-600 dark:text-gray-400" style="font-family: '{{ $settings['font_family_body'] ?? 'Inter' }}', sans-serif;">
                        Contoh teks body untuk preview font
                    </p>
                </div>
            </div>
        </div>

        {{-- Colors --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-4 flex items-center gap-2 font-semibold text-gray-800 dark:text-white">
                <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                Warna & Tema
            </h3>
            <div class="space-y-4">
                {{-- Primary Color --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Warna Utama (Primary)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="primary_color" value="{{ $settings['primary_color'] ?? '#9333ea' }}" id="primary-color-input" class="h-12 w-12 cursor-pointer rounded-xl border-2 border-gray-200 p-1 dark:border-slate-600">
                        <input type="text" value="{{ $settings['primary_color'] ?? '#9333ea' }}" id="primary-color-text" class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    </div>
                </div>

                {{-- Secondary Color --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Warna Sekunder</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="secondary_color" value="{{ $settings['secondary_color'] ?? '#a855f7' }}" id="secondary-color-input" class="h-12 w-12 cursor-pointer rounded-xl border-2 border-gray-200 p-1 dark:border-slate-600">
                        <input type="text" value="{{ $settings['secondary_color'] ?? '#a855f7' }}" id="secondary-color-text" class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    </div>
                </div>

                {{-- Color Preview --}}
                <div class="flex gap-3">
                    <div id="primary-preview" class="flex-1 rounded-xl p-4 text-center" style="background-color: {{ $settings['primary_color'] ?? '#9333ea' }}; color: white;">
                        <p class="text-sm font-medium">Primary</p>
                    </div>
                    <div id="secondary-preview" class="flex-1 rounded-xl p-4 text-center" style="background-color: {{ $settings['secondary_color'] ?? '#a855f7' }}; color: white;">
                        <p class="text-sm font-medium">Secondary</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lainnya --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-4 flex items-center gap-2 font-semibold text-gray-800 dark:text-white">
                <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Lainnya
            </h3>
            <div class="space-y-4">
                {{-- Dark Mode Toggle --}}
                <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-slate-700 dark:bg-slate-700/50">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Dark Mode</p>
                        <p class="text-xs text-gray-500">Aktifkan mode gelap untuk aplikasi</p>
                    </div>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" name="dark_mode_enabled" value="1" class="peer sr-only" {{ ($settings['dark_mode_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                        <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-purple-600 peer-checked:after:translate-x-full peer-checked:after:border-white dark:bg-slate-600"></div>
                    </label>
                </div>

                {{-- Reset Button --}}
                <div class="pt-4">
                    <a href="{{ route('settings.reset') }}" onclick="return confirm('Yakin ingin reset ke pengaturan default?')" class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reset ke Default
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="mt-6 flex items-center justify-end gap-3">
        <button type="submit" class="rounded-xl bg-purple-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 hover:bg-purple-700">
            Simpan Pengaturan
        </button>
    </div>
</form>

@push('scripts')
<script>
    // Logo preview
    document.getElementById('logo-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logo-preview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Sync color inputs
    document.getElementById('primary-color-input').addEventListener('input', function(e) {
        document.getElementById('primary-color-text').value = e.target.value;
        document.getElementById('primary-preview').style.backgroundColor = e.target.value;
    });
    document.getElementById('primary-color-text').addEventListener('input', function(e) {
        if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
            document.getElementById('primary-color-input').value = e.target.value;
            document.getElementById('primary-preview').style.backgroundColor = e.target.value;
        }
    });

    document.getElementById('secondary-color-input').addEventListener('input', function(e) {
        document.getElementById('secondary-color-text').value = e.target.value;
        document.getElementById('secondary-preview').style.backgroundColor = e.target.value;
    });
    document.getElementById('secondary-color-text').addEventListener('input', function(e) {
        if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
            document.getElementById('secondary-color-input').value = e.target.value;
            document.getElementById('secondary-preview').style.backgroundColor = e.target.value;
        }
    });

    // Font preview
    const loadedPreviewFonts = new Set();

    function loadPreviewFont(fontFamily) {
        if (loadedPreviewFonts.has(fontFamily)) {
            return;
        }

        const fontLink = document.createElement('link');
        fontLink.rel = 'stylesheet';
        fontLink.href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent(fontFamily).replace(/%20/g, '+') + ':wght@400&display=swap';
        document.head.appendChild(fontLink);
        loadedPreviewFonts.add(fontFamily);
    }

    function updateFontPreview(selectName, previewId) {
        const fontFamily = document.querySelector(selectName).value;
        loadPreviewFont(fontFamily);
        document.getElementById(previewId).style.fontFamily = "'" + fontFamily + "', sans-serif";
    }

    updateFontPreview('select[name="font_family_display"]', 'font-display-preview');
    updateFontPreview('select[name="font_family_body"]', 'font-body-preview');

    document.querySelector('select[name="font_family_display"]').addEventListener('change', function() {
        updateFontPreview('select[name="font_family_display"]', 'font-display-preview');
    });
    document.querySelector('select[name="font_family_body"]').addEventListener('change', function() {
        updateFontPreview('select[name="font_family_body"]', 'font-body-preview');
    });
</script>
@endpush
@endsection
