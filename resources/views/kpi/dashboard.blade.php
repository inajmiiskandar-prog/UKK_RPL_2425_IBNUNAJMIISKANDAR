@extends('layouts.dashboard')

@section('title', 'Dashboard KPI')
@section('page-title', 'Dashboard KPI')
@section('page-breadcrumb', 'KPI / Dashboard')

@push('styles')
<style>
    /* Animasi untuk kartu statistik */
    .stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .dark .stat-card:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
    }
</style>
@endpush

@section('content')
{{-- Filter Periode --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Periode</label>
            <select name="period_id" onchange="this.form.submit()" class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="">Semua Periode</option>
                @foreach($periods as $period)
                <option value="{{ $period->id }}" {{ $selectedPeriod && $selectedPeriod->id == $period->id ? 'selected' : '' }}>
                    {{ $period->nama }}
                </option>
                @endforeach
            </select>
        </div>
        @if($selectedPeriod)
        <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ $selectedPeriod->tanggal_mulai->format('d/m/Y') }} - {{ $selectedPeriod->tanggal_selesai->format('d/m/Y') }}
        </div>
        @endif
    </form>
</div>

{{-- 4 Kartu Statistik --}}
<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    {{-- Total Karyawan --}}
    <div class="stat-card rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Karyawan</p>
        <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($totalKaryawan) }}</p>
        @if($isAdminOrHr)
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Karyawan aktif (tanpa ADMIN)</p>
        @elseif($isAtasan)
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Bawahan Anda</p>
        @else
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Profil Anda</p>
        @endif
    </div>

    {{-- Sudah Dinilai --}}
    <div class="stat-card rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Sudah Dinilai</p>
        <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($sudahDinilai) }}</p>
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Status sudah dicek</p>
    </div>

    {{-- Belum Dinilai --}}
    <div class="stat-card rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum Dinilai</p>
        <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($belumDinilai) }}</p>
        @if($menungguReview > 0)
        <p class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">{{ number_format($menungguReview) }} menunggu review</p>
        @else
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">&nbsp;</p>
        @endif
    </div>

    {{-- Rata-rata KPI --}}
    <div class="stat-card rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Rata-rata KPI</p>
        @if($avgKpi !== null)
        <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($avgKpi, 1) }}</p>
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Skor akhir periode</p>
        @else
        <p class="mt-1 text-2xl font-bold text-gray-400">-</p>
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Belum ada data</p>
        @endif
    </div>
</div>

{{-- Chart Tren --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Tren Rata-rata Skor KPI</h3>
            @if($percentageChange !== null)
            <p class="mt-1 text-sm">
                @if($percentageChange > 0)
                <span class="text-green-600 dark:text-green-400">+{{ $percentageChange }}%</span>
                @elseif($percentageChange < 0)
                <span class="text-red-600 dark:text-red-400">{{ $percentageChange }}%</span>
                @else
                <span class="text-gray-500 dark:text-gray-400">0%</span>
                @endif
                <span class="text-gray-400 dark:text-gray-500">dibanding periode sebelumnya</span>
            </p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-500 dark:text-gray-400">Jenis:</label>
            <select id="chartType" onchange="updateChart()" class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="line">Line</option>
                <option value="bar">Bar</option>
                <option value="line" data-fill="true>Area</option>
            </select>
            <label class="text-sm text-gray-500 dark:text-gray-400">Rentang:</label>
            <select id="chartRange" onchange="updateChart()" class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="6">6 Periode</option>
                <option value="12">12 Periode</option>
                <option value="all">Semua</option>
            </select>
        </div>
    </div>

    @if(count($chartScores) > 0 && count(array_filter($chartScores, fn($v) => $v !== null)) > 0)
    <div class="relative" style="height: 300px;">
        <canvas id="trendChart"></canvas>
    </div>
    @else
    <div class="flex h-64 flex-col items-center justify-center text-gray-400 dark:text-gray-500">
        <svg class="mb-2 h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <p>Belum ada data tren untuk ditampilkan</p>
    </div>
    @endif
</div>

{{-- Tabel Hasil Terbaru --}}
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="border-b border-gray-100 px-5 py-4 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">10 Hasil Terbaru</h3>
        @if($selectedPeriod)
        <p class="text-sm text-gray-500 dark:text-gray-400">Periode: {{ $selectedPeriod->nama }}</p>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-slate-700 dark:bg-slate-700/50">
                <tr>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">#</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Nama</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Divisi</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Periode</th>
                    <th class="px-6 py-4 text-right font-semibold text-gray-600 dark:text-gray-300">Skor Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @forelse($recentResults as $result)
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700">
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('kpi.assessment.show', $result->id) }}" class="font-medium text-purple-600 hover:underline dark:text-purple-400">
                            {{ $result->user->nama ?? '-' }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $result->user->divisi ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $result->period->nama ?? '-' }}</td>
                    <td class="px-6 py-4 text-right">
                        <span class="font-semibold text-purple-600 dark:text-purple-400">
                            {{ $result->skor_akhir !== null ? number_format($result->skor_akhir, 1) : '-' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <p class="text-gray-500 dark:text-gray-400">
                            @if($selectedPeriod)
                            Belum ada hasil penilaian di periode ini
                            @else
                            Pilih periode untuk melihat hasil
                            @endif
                        </p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@if(count($chartScores) > 0 && count(array_filter($chartScores, fn($v) => $v !== null)) > 0)
@push('scripts')
<script>
    const chartLabels = @json($chartLabels);
    const chartScores = @json($chartScores);
    let trendChart = null;

    // Deteksi dark mode
    function isDarkMode() {
        return document.documentElement.classList.contains('dark');
    }

    // Warna berdasarkan tema
    function getChartColors() {
        const dark = isDarkMode();
        return {
            borderColor: dark ? 'rgba(147, 51, 234, 1)' : 'rgba(147, 51, 234, 1)',
            backgroundColor: dark ? 'rgba(147, 51, 234, 0.1)' : 'rgba(147, 51, 234, 0.1)',
            pointBackgroundColor: dark ? 'rgba(147, 51, 234, 1)' : 'rgba(147, 51, 234, 1)',
            textColor: dark ? '#e2e8f0' : '#334155',
            gridColor: dark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
        };
    }

    function createChart(type = 'line', range = 'all') {
        const colors = getChartColors();

        // Filter data berdasarkan rentang
        let labels = chartLabels;
        let scores = chartScores;

        if (range !== 'all') {
            const limit = parseInt(range);
            labels = labels.slice(-limit);
            scores = scores.slice(-limit);
        }

        // Hapus data null dari akhir array jika ada
        while (scores.length > 0 && scores[scores.length - 1] === null) {
            labels.pop();
            scores.pop();
        }

        const ctx = document.getElementById('trendChart').getContext('2d');

        if (trendChart) {
            trendChart.destroy();
        }

        const config = {
            type: type === 'area' ? 'line' : type,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Rata-rata Skor',
                    data: scores,
                    borderColor: colors.borderColor,
                    backgroundColor: type === 'bar'
                        ? 'rgba(147, 51, 234, 0.6)'
                        : (type === 'area' ? colors.backgroundColor : 'transparent'),
                    fill: type === 'area' || type === 'bar',
                    tension: 0.3,
                    pointBackgroundColor: colors.pointBackgroundColor,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Skor: ' + context.parsed.y.toFixed(1);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: colors.gridColor },
                        ticks: { color: colors.textColor }
                    },
                    x: {
                        grid: { color: colors.gridColor },
                        ticks: { color: colors.textColor }
                    }
                }
            }
        };

        trendChart = new Chart(ctx, config);
    }

    function updateChart() {
        const type = document.getElementById('chartType').value;
        const range = document.getElementById('chartRange').value;
        createChart(type, range);
    }

    // Inisialisasi saat halaman load
    document.addEventListener('DOMContentLoaded', function() {
        createChart('line', 'all');

        // Update chart saat dark mode berubah
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    updateChart();
                }
            });
        });
        observer.observe(document.documentElement, { attributes: true });
    });
</script>
@endpush
@endif
