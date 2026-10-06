@extends('layouts.dashboard')

@section('title', 'Histori KPI')
@section('page-title', 'Histori KPI')
@section('page-breadcrumb', 'KPI / Histori')

@section('content')
{{-- GET filters remain in the query string when navigating between pages. --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <form method="GET" class="grid min-w-0 grid-cols-1 items-end gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @if(auth()->user()->role === 'ADMIN' && $allUsers->count() > 0)
        <div class="min-w-0">
            <label for="history_user_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Karyawan</label>
            <select id="history_user_id" name="user_id" class="w-full min-w-0 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="">Semua Karyawan</option>
                @foreach($allUsers as $u)
                <option value="{{ $u->id_user }}" {{ $targetUser->id_user == $u->id_user ? 'selected' : '' }}>
                    {{ $u->nama }}
                </option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="min-w-0">
            <label for="history_period_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Periode</label>
            <select id="history_period_id" name="period_id" class="w-full min-w-0 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="">Semua Periode</option>
                @foreach($periods as $p)
                <option value="{{ $p->id }}" {{ $selectedPeriod && $selectedPeriod->id == $p->id ? 'selected' : '' }}>
                    {{ $p->nama }} ({{ $p->status }})
                </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-0">
            <label for="history_per_page" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Per Halaman</label>
            <select id="history_per_page" name="per_page" class="w-full min-w-0 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @foreach([10, 25, 50, 100] as $pageSize)
                <option value="{{ $pageSize }}" {{ $perPage === $pageSize ? 'selected' : '' }}>{{ $pageSize }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800">
                Terapkan
            </button>
            <a href="{{ route('kpi.assessment.history') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Info User --}}
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="flex items-center gap-4">
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-purple-100 text-xl font-bold text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
            {{ substr($targetUser->nama, 0, 1) }}
        </div>
        <div>
            <h3 class="font-display text-xl font-bold text-gray-800 dark:text-white">{{ $targetUser->nama }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $targetUser->divisi ?? '-' }} / {{ $targetUser->jabatan ?? '-' }} / NIK: {{ $targetUser->nik ?? '-' }}
            </p>
        </div>
    </div>
</div>

{{-- Chart Section --}}
@if(count($chartScores) > 0 && count(array_filter($chartScores)) > 0)
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <h3 class="mb-4 font-semibold text-gray-800 dark:text-white">Tren Skor KPI</h3>
    <canvas id="kpiChart" height="80"></canvas>
</div>
@endif

{{-- Histori Table --}}
<div class="mb-3 flex flex-col gap-2 text-sm text-gray-600 dark:text-gray-300 sm:flex-row sm:items-center sm:justify-between">
    <p>
        Menampilkan {{ $assessments->firstItem() ?? 0 }}-{{ $assessments->lastItem() ?? 0 }} dari {{ $assessments->total() }}
    </p>
    <div class="max-w-full overflow-x-auto">
        {{ $assessments->links() }}
    </div>
</div>
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="overflow-x-auto">
        <table class="min-w-[1100px] w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-slate-700 dark:bg-slate-700/50">
                <tr>
                    <th class="whitespace-nowrap px-3 py-3 font-semibold text-gray-600 dark:text-gray-300">#</th>
                    <th class="whitespace-nowrap px-3 py-3 font-semibold text-gray-600 dark:text-gray-300">Periode</th>
                    <th class="whitespace-nowrap px-3 py-3 font-semibold text-gray-600 dark:text-gray-300">Tanggal Dibuat</th>
                    <th class="whitespace-nowrap px-3 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    <th class="whitespace-nowrap px-3 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Skor Soft Skill</th>
                    <th class="whitespace-nowrap px-3 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Skor Hard Skill</th>
                    <th class="whitespace-nowrap px-3 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Skor Akhir</th>
                    <th class="whitespace-nowrap px-3 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Grade</th>
                    <th class="whitespace-nowrap px-3 py-3 font-semibold text-gray-600 dark:text-gray-300">Nama Atasan</th>
                    <th class="whitespace-nowrap px-3 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @forelse($assessments as $a)
                @php
                    $detailUrl = route('kpi.assessment.show', $a->id);
                    $sClass = match($a->status) {
                        'pending' => 'bg-gray-100 text-gray-700 dark:bg-slate-700 dark:text-gray-300',
                        'self_done' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
                        'atasan_done' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
                        'selesai' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                        'menunggu_review' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
                        'sudah_dicek' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                        default => 'bg-gray-100 text-gray-700 dark:bg-slate-700 dark:text-gray-300',
                    };
                    $sLabel = match($a->status) {
                        'pending' => 'Pending',
                        'self_done' => 'Self Selesai',
                        'atasan_done' => 'Atasan Selesai',
                        'selesai' => 'Selesai',
                        'menunggu_review' => 'Menunggu review atasan',
                        'sudah_dicek' => 'Sudah selesai dinilai',
                        default => $a->status,
                    };
                    $gradeClass = match($a->grade()) {
                        'A' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                        'B' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                        'C' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                        'D' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
                        'E' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                        default => 'bg-gray-100 text-gray-700 dark:bg-slate-700 dark:text-gray-300',
                    };
                @endphp
                <tr ondblclick="window.location.href='{{ $detailUrl }}'" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700">
                    <td class="whitespace-nowrap px-3 py-3 text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                    <td class="whitespace-nowrap px-3 py-3 font-medium text-gray-800 dark:text-white">{{ $a->period?->nama ?? '-' }}</td>
                    <td class="whitespace-nowrap px-3 py-3 text-gray-500 dark:text-gray-400">{{ $a->created_at?->format('d M Y') ?? '-' }}</td>
                    <td class="whitespace-nowrap px-3 py-3">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $sClass }}">
                            {{ $sLabel }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-gray-600 dark:text-gray-400">
                        {{ $a->history_soft_skill_score !== null ? number_format($a->history_soft_skill_score, 1) : '-' }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-gray-600 dark:text-gray-400">
                        {{ $a->history_hard_skill_score !== null ? number_format($a->history_hard_skill_score, 1) : '-' }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums">
                        @if($a->skor_akhir !== null)
                        <span class="font-semibold text-purple-600 dark:text-purple-400">{{ number_format($a->skor_akhir, 1) }}</span>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-3 py-3 text-center">
                        <span class="inline-flex min-w-8 justify-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $gradeClass }}">{{ $a->grade() }}</span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-3 text-gray-600 dark:text-gray-300">{{ $a->atasan?->nama ?? '-' }}</td>
                    <td class="whitespace-nowrap px-3 py-3 text-right">
                        <a href="{{ $detailUrl }}" class="inline-flex min-h-9 items-center rounded-lg bg-purple-100 px-3 py-1.5 text-xs font-semibold text-purple-700 hover:bg-purple-200 dark:bg-purple-900/40 dark:text-purple-300 dark:hover:bg-purple-900/70" aria-label="Detail penilaian periode {{ $a->period?->nama ?? '-' }}">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="px-3 py-12 text-center"><p class="text-gray-500 dark:text-gray-400">Belum ada histori penilaian</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if($assessments->hasPages())
<div class="mt-4 flex justify-end overflow-x-auto">
    {{ $assessments->links() }}
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@if(count($chartScores) > 0 && count(array_filter($chartScores)) > 0)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('kpiChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Skor KPI',
                data: @json(array_map(fn($v) => $v !== null ? (float)$v : null, $chartScores)),
                borderColor: '#9333ea',
                backgroundColor: 'rgba(147, 51, 234, 0.1)',
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#9333ea',
                pointRadius: 5,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Skor' }
                }
            }
        }
    });
});
</script>
@endpush
@endif
