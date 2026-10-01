<?php

/**
 * Controller untuk Dashboard KPI
 * Menampilkan ringkasan KPI: statistik, tren, dan hasil terbaru
 *
 * Hak akses:
 * - ADMIN: melihat semua data karyawan aktif (exclude ADMIN sendiri)
 * - Role lain: dirinya sendiri + bawahan langsung
 */

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiAssessment;
use App\Models\KpiPeriod;
use App\Models\User;
use Illuminate\Http\Request;

class KpiDashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard KPI
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Ambil periode untuk filter
        $periods = KpiPeriod::orderByDesc('tanggal_mulai')->get();

        // Default: periode terbaru berdasarkan tanggal_mulai
        $selectedPeriodId = $request->period_id ?? $periods->first()?->id;
        $selectedPeriod = $selectedPeriodId ? $periods->find($selectedPeriodId) : null;

        // =====================================================
        // QUERY BERDASARKAN HAK AKSES
        // =====================================================

        if ($user->role === 'ADMIN') {
            // ADMIN: semua user aktif, exclude ADMIN sendiri dari hitungan
            $userScope = User::where('status', true)->where('role', '!=', 'ADMIN');
            $allUserIds = User::where('status', true)->pluck('id_user'); // Untuk chart/rekap
        } else {
            // Role lain: diri sendiri + bawahan langsung
            $bawahanIds = $user->bawahan()->where('status', true)->pluck('id_user');
            $userScope = User::whereIn('id_user', $bawahanIds->push($user->id_user));
            $allUserIds = $userScope->pluck('id_user');
        }

        // =====================================================
        // STATISTIK
        // =====================================================

        // Total Karyawan (berdasarkan hak akses)
        $totalKaryawan = (clone $userScope)->count();

        // Sudah Dinilai di periode terpilih (status sudah_dicek)
        $userIds = (clone $userScope)->pluck('id_user');
        $sudahDinilai = 0;
        $belumDinilai = 0;
        $menungguReview = 0;

        if ($selectedPeriod) {
            $assessmentsInPeriod = KpiAssessment::where('kpi_period_id', $selectedPeriod->id)
                ->whereIn('user_id', $userIds)
                ->get();

            $sudahDinilai = $assessmentsInPeriod->where('status', 'sudah_dicek')->count();

            // Menunggu review
            $menungguReview = $assessmentsInPeriod->where('status', 'menunggu_review')->count();

            // Belum dinilai = total user - yang sudah ada assessment di periode ini
            $userIdsWithAssessment = $assessmentsInPeriod->pluck('user_id');
            $belumDinilai = max(0, $totalKaryawan - $userIdsWithAssessment->count());
        }

        // Rata-rata KPI (hanya status sudah_dicek)
        $avgKpi = KpiAssessment::where('kpi_period_id', $selectedPeriodId)
            ->whereIn('user_id', $userIds)
            ->where('status', 'sudah_dicek')
            ->whereNotNull('skor_akhir')
            ->avg('skor_akhir');

        // =====================================================
        // DATA UNTUK CHART TREN
        // =====================================================

        // Ambil periode yang ada datanya (untuk chart tren)
        $chartPeriods = KpiPeriod::whereHas('assessments', function ($query) use ($userIds) {
            $query->whereIn('user_id', $userIds)
                  ->where('status', 'sudah_dicek')
                  ->whereNotNull('skor_akhir');
        })
        ->orderBy('tanggal_mulai')
        ->get();

        // Hitung rata-rata per periode untuk chart
        $chartLabels = [];
        $chartScores = [];

        foreach ($chartPeriods as $period) {
            $chartLabels[] = $period->nama;
            $avg = KpiAssessment::where('kpi_period_id', $period->id)
                ->whereIn('user_id', $userIds)
                ->where('status', 'sudah_dicek')
                ->whereNotNull('skor_akhir')
                ->avg('skor_akhir');
            $chartScores[] = $avg ? round($avg, 2) : null;
        }

        // Hitung persentase perubahan dibanding periode sebelumnya
        $percentageChange = null;
        if (count($chartScores) >= 2) {
            $currentScore = $chartScores[count($chartScores) - 1];
            $previousScore = $chartScores[count($chartScores) - 2];
            if ($previousScore && $previousScore > 0) {
                $percentageChange = round((($currentScore - $previousScore) / $previousScore) * 100, 1);
            }
        }

        // =====================================================
        // 10 HASIL TERBARU DI PERIODE TERPILIH
        // =====================================================

        $recentResults = [];
        if ($selectedPeriod) {
            $recentResults = KpiAssessment::with(['user', 'period'])
                ->where('kpi_period_id', $selectedPeriod->id)
                ->whereIn('user_id', $userIds)
                ->whereNotNull('skor_akhir')
                ->orderByDesc('updated_at')
                ->limit(10)
                ->get();
        }

        return view('kpi.dashboard', compact(
            'totalKaryawan',
            'sudahDinilai',
            'belumDinilai',
            'menungguReview',
            'avgKpi',
            'periods',
            'selectedPeriod',
            'chartLabels',
            'chartScores',
            'percentageChange',
            'recentResults'
        ));
    }
}
