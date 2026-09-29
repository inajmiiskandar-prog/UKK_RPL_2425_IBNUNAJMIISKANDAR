@extends('layouts.dashboard')

@section('title', 'Histori KPI')
@section('page-title', 'Histori KPI')
@section('page-breadcrumb', 'KPI / Histori')

@section('content')
{{-- Filter (hanya untuk Admin) --}}
@if(auth()->user()->role === 'ADMIN' && $allUsers->count() > 0)
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Karyawan</label>
            <select name="user_id" onchange="this.form.submit()" class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="">Semua Karyawan</option>
                @foreach($allUsers as $u)
                <option value="{{ $u->id_user }}" {{ $targetUser->id_user == $u->id_user ? 'selected' : '' }}>
                    {{ $u->nama }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Periode</label>
            <select name="period_id" onchange="this.form.submit()" class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="">Semua Periode</option>
                @foreach($periods as $p)
                <option value="{{ $p->id }}" {{ $selectedPeriod && $selectedPeriod->id == $p->id ? 'selected' : '' }}>
                    {{ $p->nama }} ({{ $p->status }})
                </option>
                @endforeach
            </select>
        </div>
    </form>
</div>
@endif

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
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-slate-700 dark:bg-slate-700/50">
                <tr>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">#</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Periode</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Tanggal</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Skor Self</th>
                    <th class="px-6 py-4 font-semibold text-gray-600 dark:text-gray-300">Skor Atasan</th>
                    <th class="px-6 py-4 text-right font-semibold text-gray-600 dark:text-gray-300">Skor Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @forelse($assessments as $a)
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700">
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">{{ $a->period->nama ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                        {{ $a->period ? $a->period->tanggal_mulai->format('d/m/Y') . ' - ' . $a->period->tanggal_selesai->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $sClass = match($a->status) {
                                'pending' => 'bg-gray-100 text-gray-700',
                                'self_done' => 'bg-yellow-100 text-yellow-700',
                                'atasan_done' => 'bg-orange-100 text-orange-700',
                                'selesai' => 'bg-green-100 text-green-700',
                                'menunggu_review' => 'bg-yellow-100 text-yellow-700',
                                'sudah_dicek' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-700',
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
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $sClass }}">
                            {{ $sLabel }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                        {{ $a->selfScores->count() > 0 ? number_format($a->selfScores->avg('skor'), 1) : '-' }}
                    </td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                        {{ $a->atasanScores->count() > 0 ? number_format($a->atasanScores->avg('skor'), 1) : '-' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($a->skor_akhir !== null)
                        <span class="font-semibold text-purple-600 dark:text-purple-400">{{ number_format($a->skor_akhir, 1) }}</span>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center"><p class="text-gray-500 dark:text-gray-400">Belum ada histori penilaian</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
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
