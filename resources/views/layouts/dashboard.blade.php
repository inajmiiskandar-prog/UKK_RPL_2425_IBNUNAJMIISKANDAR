<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ App\Models\Setting::get('app_name', 'Passnet') }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Dynamic Google Fonts -->
    <?php
        $fontDisplay = App\Models\Setting::get('font_family_display', 'Sora');
        $fontBody = App\Models\Setting::get('font_family_body', 'Inter');
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $fontDisplay) }}:wght@400;500;600;700&family={{ str_replace(' ', '+', $fontBody) }}:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Dynamic Colors -->
    <?php
        $primaryColor = App\Models\Setting::get('primary_color', '#9333ea');
        $secondaryColor = App\Models\Setting::get('secondary_color', '#a855f7');

        // Generate color variations
        if (!function_exists('hexToRgb')) {
            function hexToRgb($hex) {
                $hex = str_replace('#', '', $hex);
                if (strlen($hex) == 3) {
                    $hex = str_repeat($hex, 2);
                }
                return hexdec($hex);
            }
        }

        $primaryRgb = [];
        if(preg_match('/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i', $primaryColor, $matches)) {
            $primaryRgb = [hexdec($matches[1]), hexdec($matches[2]), hexdec($matches[3])];
        } else {
            $primaryRgb = [147, 51, 234]; // default purple
        }
    ?>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'display': ['{{ $fontDisplay }}', 'ui-sans-serif', 'sans-serif'],
                        'body': ['{{ $fontBody }}', 'ui-sans-serif', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '{{ $primaryColor }}',
                            100: '{{ $primaryColor }}',
                            200: '{{ $primaryColor }}',
                            300: '{{ $primaryColor }}',
                            400: '{{ $primaryColor }}',
                            500: '{{ $secondaryColor }}',
                            600: '{{ $primaryColor }}',
                            700: '{{ $primaryColor }}',
                            800: '{{ $primaryColor }}',
                            900: '{{ $primaryColor }}',
                            950: '{{ $primaryColor }}',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Custom Styles -->
    <style>
        .font-display { font-family: '{{ $fontDisplay }}', ui-sans-serif, sans-serif; }
        .font-body { font-family: '{{ $fontBody }}', ui-sans-serif, sans-serif; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Dark mode scrollbar */
        .dark ::-webkit-scrollbar-track {
            background: #1e293b;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }

        /* Sidebar transition */
        .sidebar-transition {
            transition: all 0.3s ease;
        }

        /* Active menu item - dynamic primary color */
        .menu-item.active {
            background: linear-gradient(to right, {{ $primaryColor }}, {{ $secondaryColor }});
            color: white;
        }
        .menu-item.active svg {
            color: white;
        }

        /* Card hover */
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        /* Table row hover */
        .table-row:hover {
            background-color: #f8fafc;
        }
        .dark .table-row:hover {
            background-color: #1e293b;
        }

        /* Primary color accent */
        .text-primary { color: {{ $primaryColor }}; }
        .bg-primary { background-color: {{ $primaryColor }}; }
        .border-primary { border-color: {{ $primaryColor }}; }
    </style>

    @stack('styles')
</head>
<body class="font-body overflow-x-hidden bg-gray-50 dark:bg-slate-900">
    <!-- Overlay for mobile sidebar -->
    <div id="sidebarOverlay" class="fixed inset-0 z-[9990] hidden bg-black/50 lg:hidden" onclick="toggleSidebar()"></div>

    <div class="min-h-screen flex">

        {{-- ========================================== --}}
        {{-- SIDEBAR - STICKY ON DESKTOP                        --}}
        {{-- ========================================== --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-[9991] flex h-screen w-64 flex-shrink-0 -translate-x-full flex-col bg-white shadow-xl sidebar-transition dark:bg-slate-800 lg:sticky lg:top-0 lg:z-auto lg:translate-x-0">

            {{-- Logo & Brand (Fixed at top) --}}
            <div class="flex h-16 flex-shrink-0 items-center justify-between border-b border-gray-100 dark:border-slate-700 px-6">
                <div class="flex items-center gap-3">
                    <img src="{{ asset(App\Models\Setting::get('logo_path', 'images/logo-passnet.png')) }}"
                         alt="Logo"
                         class="object-cover"
                         style="width: {{ App\Models\Setting::get('logo_width', 40) }}px; height: {{ App\Models\Setting::get('logo_height', 40) }}px;">
                    <div>
                        <h1 class="font-display text-lg font-bold text-gray-800 dark:text-white">{{ App\Models\Setting::get('app_name', 'Passnet') }}</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ App\Models\Setting::get('app_tagline', 'ISP Management') }}</p>
                    </div>
                </div>
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation (Scrollable) --}}
            <nav class="mt-6 px-3 flex-1 overflow-y-auto">

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                   class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all mb-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                @php
                    // Match Master Data navigation to the route role matrix.
                    $masterDataRole = auth()->user()?->role;
                    $canViewArea = $masterDataRole === 'ADMIN';
                    $canViewPop = $masterDataRole === 'ADMIN';
                    $canViewOlt = in_array($masterDataRole, ['ADMIN', 'LEADER'], true);
                    $canViewOdp = in_array($masterDataRole, ['ADMIN', 'LEADER'], true);
                    $canViewOnt = in_array($masterDataRole, ['ADMIN', 'LOGISTIK', 'TEKNISI'], true);
                    $canViewPortPon = in_array($masterDataRole, ['ADMIN', 'TEKNISI'], true);
                    $canViewMaterial = in_array($masterDataRole, ['ADMIN', 'LOGISTIK', 'TEKNISI'], true);
                    $canViewPaket = in_array($masterDataRole, ['ADMIN', 'LOGISTIK'], true);
                    $canViewAnyMasterData = $canViewArea || $canViewPop || $canViewOlt || $canViewOdp
                        || $canViewOnt || $canViewPortPon || $canViewMaterial || $canViewPaket;
                @endphp

                @if($canViewAnyMasterData)
                <div class="mt-6 mb-2 px-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Master Data</p>
                </div>

                <div class="space-y-1">
                    @if($canViewArea)
                    <a href="{{ route('masterdata.area.index') }}"
                       class="menu-item {{ request()->routeIs('masterdata.area.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Area</span>
                    </a>
                    @endif

                    @if($canViewPop)
                    <a href="{{ route('masterdata.pop.index') }}"
                       class="menu-item {{ request()->routeIs('masterdata.pop.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>POP</span>
                    </a>
                    @endif

                    @if($canViewOlt)
                    <a href="{{ route('masterdata.olt.index') }}"
                       class="menu-item {{ request()->routeIs('masterdata.olt.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                        </svg>
                        <span>OLT</span>
                    </a>
                    @endif

                    @if($canViewOdp)
                    <a href="{{ route('masterdata.odp.index') }}"
                       class="menu-item {{ request()->routeIs('masterdata.odp.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                        </svg>
                        <span>ODP</span>
                    </a>
                    @endif

                    @if($canViewOnt)
                    <a href="{{ route('masterdata.ont.index') }}"
                       class="menu-item {{ request()->routeIs('masterdata.ont.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>ONT</span>
                    </a>
                    @endif

                    @if($canViewPortPon)
                    <a href="{{ route('masterdata.port-pon.index') }}"
                       class="menu-item {{ request()->routeIs('masterdata.port-pon.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        <span>Port PON</span>
                    </a>
                    @endif

                    @if($canViewMaterial)
                    <a href="{{ route('masterdata.material.index') }}"
                       class="menu-item {{ request()->routeIs('masterdata.material.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span>Material</span>
                    </a>
                    @endif

                    @if($canViewPaket)
                    <a href="{{ route('masterdata.paket.index') }}"
                       class="menu-item {{ request()->routeIs('masterdata.paket.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span>Paket</span>
                    </a>
                    @endif
                </div>
                @endif

                {{-- Transaksi Section --}}
                <div class="mt-6 mb-2 px-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Transaksi</p>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('jaringan.fab.index') }}"
                       class="menu-item {{ request()->routeIs('jaringan.fab.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Pelanggan (FAB)</span>
                    </a>

                    <a href="{{ route('jaringan.baa.index') }}"
                       class="menu-item {{ request()->routeIs('jaringan.baa.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>BAA</span>
                    </a>
                </div>

                {{-- Lainnya Section --}}
                <div class="mt-6 mb-2 px-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Lainnya</p>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('users.index') }}"
                       class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>Users</span>
                    </a>

                </div>

                @php
                    // Keep sidebar visibility aligned with the KPI route middleware.
                    $kpiRole = auth()->user()?->role;
                    $canViewKpiAssessment = auth()->check();
                    $canViewSoftSkill = $kpiRole === 'ADMIN';
                    $canViewHardSkill = in_array($kpiRole, ['ADMIN', 'LEADER'], true);
                    $canViewKpiExport = $kpiRole === 'ADMIN';
                @endphp

                @if($canViewKpiAssessment || $canViewSoftSkill || $canViewHardSkill || $canViewKpiExport)
                <div class="mt-6 mb-2 px-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">KPI</p>
                </div>

                <div class="space-y-1">
                    {{-- Master Data KPI --}}
                    @if($canViewSoftSkill)
                    <a href="{{ route('kpi.soft-skill.index') }}"
                       class="menu-item {{ request()->routeIs('kpi.soft-skill.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Soft Skill</span>
                    </a>
                    @endif

                    @if($canViewHardSkill)
                    <a href="{{ route('kpi.hard-skill.index') }}"
                       class="menu-item {{ request()->routeIs('kpi.hard-skill.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <span>Hard Skill</span>
                    </a>
                    @endif

                    @if($canViewKpiAssessment)
                    <a href="{{ route('kpi.assessment.index') }}"
                       class="menu-item {{ request()->routeIs('kpi.assessment.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span>Penilaian KPI</span>
                    </a>

                    <a href="{{ route('kpi.assessment.history') }}"
                       class="menu-item {{ request()->routeIs('kpi.assessment.history') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span>Histori KPI</span>
                    </a>
                    @endif

                    @if($canViewKpiExport)
                    <a href="{{ route('kpi.report.index') }}"
                       class="menu-item {{ request()->routeIs('kpi.report.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Export Data</span>
                    </a>
                    @endif
                </div>
                @endif

                {{-- Pengaturan Section (Paling Bawah) --}}
                <div class="mt-6 mb-2 px-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pengaturan</p>
                </div>

                <div class="space-y-1 mb-6">
                    <a href="{{ route('settings.index') }}"
                       class="menu-item {{ request()->routeIs('settings.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Pengaturan</span>
                    </a>
                </div>
            </nav>
        </aside>

        {{-- ========================================== --}}
        {{-- MAIN CONTENT                             --}}
        {{-- ========================================== --}}
        <div class="flex min-w-0 flex-1 flex-col">

            {{-- Top Navbar --}}
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-100 bg-white/80 px-4 backdrop-blur-md dark:border-slate-700 dark:bg-slate-800/80 sm:px-6">
                {{-- Left: Mobile menu & Page title --}}
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div>
                        <h2 class="font-display text-lg font-semibold text-gray-800 dark:text-white">@yield('page-title', 'Dashboard')</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">@yield('page-breadcrumb', '')</p>
                    </div>
                </div>

                {{-- Right: Actions --}}
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="hidden items-center rounded-full bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-200 sm:flex">
                        <span id="dashboardDateTime"></span>
                    </div>

                    {{-- Dark Mode Toggle --}}
                    <button onclick="toggleDarkMode()" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-slate-700" title="Toggle Dark Mode">
                        <svg id="sunIcon" class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg id="moonIcon" class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>

                    {{-- Notifications: ADMIN lihat semua, role lain hanya miliknya --}}
                    @php
                        $notificationReadAt = session('notifications_read_at');
                        if (Auth::check()) {
                            $currentUserId = Auth::user()->id_user;
                            $isAdmin = Auth::user()->role === 'ADMIN';
                            if ($isAdmin) {
                                $notifications = App\Models\ActivityLog::with('user')
                                    ->latest('createdAt')
                                    ->limit(5)
                                    ->get();
                                $unreadNotifications = $notificationReadAt
                                    ? App\Models\ActivityLog::where('createdAt', '>', $notificationReadAt)->count()
                                    : App\Models\ActivityLog::count();
                            } else {
                                $notifications = App\Models\ActivityLog::with('user')
                                    ->where('id_user', $currentUserId)
                                    ->latest('createdAt')
                                    ->limit(5)
                                    ->get();
                                $unreadNotifications = $notificationReadAt
                                    ? App\Models\ActivityLog::where('id_user', $currentUserId)
                                        ->where('createdAt', '>', $notificationReadAt)
                                        ->count()
                                    : App\Models\ActivityLog::where('id_user', $currentUserId)->count();
                            }
                        } else {
                            $notifications = collect();
                            $unreadNotifications = 0;
                        }
                    @endphp
                    <div class="relative">
                        <button type="button" onclick="toggleNotificationMenu()" aria-label="Buka notifikasi" aria-expanded="false" class="relative rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-slate-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($unreadNotifications > 0)
                                <span id="notificationBadge" class="absolute -right-0.5 -top-0.5 min-w-4 rounded-full bg-red-500 px-1 text-center text-[10px] font-bold leading-4 text-white">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                            @endif
                        </button>

                        <div id="notificationDropdown" class="absolute right-0 top-full z-50 mt-2 hidden w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-xl border border-gray-100 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-800 sm:w-96">
                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-slate-700">
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Notifikasi</h3>
                                @if($unreadNotifications > 0)
                                    <form method="POST" action="{{ route('notifications.read') }}" onsubmit="markNotificationsRead(event)">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium text-primary hover:underline">Tandai sudah dibaca</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse($notifications as $notification)
                                    @php
                                        $notificationUrl = match (true) {
                                            str_starts_with($notification->type, 'USER_') => route('users.index'),
                                            str_starts_with($notification->type, 'FAB_') => route('jaringan.fab.index'),
                                            str_starts_with($notification->type, 'BAA_') => route('jaringan.baa.index'),
                                            str_starts_with($notification->type, 'AREA_') => route('masterdata.area.index'),
                                            str_starts_with($notification->type, 'POP_') => route('masterdata.pop.index'),
                                            str_starts_with($notification->type, 'OLT_') => route('masterdata.olt.index'),
                                            str_starts_with($notification->type, 'ODP_') => route('masterdata.odp.index'),
                                            str_starts_with($notification->type, 'ONT_') => route('masterdata.ont.index'),
                                            str_starts_with($notification->type, 'PORTPON_') => route('masterdata.port-pon.index'),
                                            str_starts_with($notification->type, 'PAKET_') => route('masterdata.paket.index'),
                                            str_starts_with($notification->type, 'MATERIAL_') => route('masterdata.material.index'),
                                            $notification->type === 'SETTINGS_UPDATED' => route('settings.index'),
                                            default => route('dashboard'),
                                        };
                                    @endphp
                                    <a href="{{ $notificationUrl }}" class="block border-b border-gray-100 px-4 py-3 last:border-0 hover:bg-gray-50 dark:border-slate-700 dark:hover:bg-slate-700/50">
                                        <p class="text-sm text-gray-700 dark:text-gray-200">{{ $notification->description }}</p>
                                        <p class="mt-1 text-xs text-gray-400">{{ $notification->user->nama ?? 'Sistem' }} &middot; {{ $notification->createdAt?->diffForHumans() }}</p>
                                    </a>
                                @empty
                                    <p class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada notifikasi.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- User Dropdown --}}
                    <div class="relative">
                        <button onclick="toggleUserMenu()" class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-gray-100 dark:hover:bg-slate-700">
                            @if(Auth::user()->foto)
                                <img src="{{ Storage::url(Auth::user()->foto) }}" alt="Avatar {{ Auth::user()->nama ?? 'User' }}" class="h-8 w-8 rounded-full object-cover">
                            @else
                                <div class="h-8 w-8 rounded-full bg-gradient-to-br {{ 'from-primary-500' }} to-pink-500 flex items-center justify-center text-white font-semibold text-sm" style="background: linear-gradient(to bottom right, {{ $primaryColor }}, {{ $secondaryColor }});">
                                    {{ substr(Auth::user()->nama ?? 'U', 0, 1) }}
                                </div>
                            @endif
                            <div class="hidden sm:block">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ Auth::user()->nama ?? 'User' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->role ?? 'User' }}</p>
                            </div>
                            <svg class="hidden sm:block h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div id="userDropdown" class="absolute right-0 top-full mt-2 w-48 rounded-xl bg-white py-2 shadow-xl dark:bg-slate-800 hidden border border-gray-100 dark:border-slate-700">
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-slate-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Profil Saya</span>
                            </a>
                            <hr class="my-2 border-gray-100 dark:border-slate-700">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-4 sm:p-6">
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="border-t border-gray-100 bg-white px-6 py-4 dark:border-slate-700 dark:bg-slate-800">
                <div class="flex flex-col items-center justify-between gap-2 sm:flex-row">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        &copy; {{ date('Y') }} {{ App\Models\Setting::get('app_name', 'PT Pass Internet Indonesia') }}. All rights reserved.
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Versi 1.0.0 | Develop by IT Department
                    </p>
                </div>
            </footer>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // User Dropdown Toggle
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Notification Dropdown Toggle
        function toggleNotificationMenu() {
            const dropdown = document.getElementById('notificationDropdown');
            const button = document.querySelector('[aria-label="Buka notifikasi"]');
            const isHidden = dropdown.classList.toggle('hidden');
            button.setAttribute('aria-expanded', String(!isHidden));
        }

        function markNotificationsRead(event) {
            event.preventDefault();
            const form = event.currentTarget;
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).then(response => {
                if (response.ok) {
                    document.getElementById('notificationBadge')?.remove();
                    form.remove();
                }
            });
        }

        // Dark Mode Toggle
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.setItem('darkMode', html.classList.contains('dark'));
        }

        // Check saved dark mode
        if (localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }

        function updateDashboardDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('dashboardDateTime').textContent = now.toLocaleDateString('id-ID', dateOptions);
        }

        updateDashboardDateTime();

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            const userDropdown = document.getElementById('userDropdown');
            if (!e.target.closest('[onclick="toggleUserMenu()"]') && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }

            const notificationDropdown = document.getElementById('notificationDropdown');
            if (!e.target.closest('[aria-label="Buka notifikasi"]') && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.add('hidden');
                document.querySelector('[aria-label="Buka notifikasi"]')?.setAttribute('aria-expanded', 'false');
            }
        });

        // CSRF Token for AJAX
        window.csrfToken = '{{ csrf_token() }}';
    </script>

    @stack('scripts')
</body>
</html>
