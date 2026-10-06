<div class="mb-6 inline-flex items-center gap-1 rounded-2xl border border-gray-100 bg-white p-1.5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <a href="{{ route('dashboard') }}"
       class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-purple-600 text-white shadow' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-slate-700' }}">
        Dashboard
    </a>
    <a href="{{ route('kpi.dashboard') }}"
       class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ request()->routeIs('kpi.dashboard') ? 'bg-purple-600 text-white shadow' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-slate-700' }}">
        KPI Pegawai
    </a>
</div>