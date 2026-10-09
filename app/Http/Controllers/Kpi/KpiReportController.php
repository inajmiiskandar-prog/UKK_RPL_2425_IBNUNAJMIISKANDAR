<?php

/**
 * Controller untuk Export Data KPI
 * Export rekap skor KPI ke format yang bisa didownload
 *
 * Menu: LAPORAN -> Export Data
 */

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiAssessment;
use App\Models\KpiPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

class KpiReportController extends Controller
{
    private const REPORT_COLUMNS = [
        'no' => 'No',
        'nik' => 'NIK',
        'nama_karyawan' => 'Nama Karyawan',
        'divisi' => 'Divisi',
        'jabatan' => 'Jabatan',
        'periode' => 'Periode',
        'skor_soft_skill' => 'Skor Soft Skill',
        'skor_hard_skill' => 'Skor Hard Skill',
        'skor_self' => 'Skor Self',
        'skor_atasan' => 'Skor Atasan',
        'skor_akhir' => 'Skor Akhir',
        'grade' => 'Grade',
        'status' => 'Status',
        'disetujui_oleh' => 'Disetujui Oleh',
    ];

    /**
     * Halaman utama export
     */
    public function index(Request $request)
    {
        $periods = KpiPeriod::orderByDesc('tanggal_mulai')->get();
        $selectedPeriod = $request->period_id
            ? KpiPeriod::find($request->period_id)
            : KpiPeriod::where('status', 'selesai')->orWhere('status', 'aktif')->first();
        $assessments = $selectedPeriod
            ? $this->assessmentsForPeriod($selectedPeriod)
            : new EloquentCollection();
        $rows = $selectedPeriod ? $this->reportRows($assessments, $selectedPeriod) : [];
        $stats = $this->reportStats($assessments);

        return view('kpi.report.index', compact('periods', 'selectedPeriod', 'rows', 'stats'))
            ->with('columns', self::REPORT_COLUMNS);
    }

    /**
     * Generate preview laporan sebelum export
     */
    public function preview(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:kpi_periods,id',
        ]);

        $period = KpiPeriod::findOrFail($request->period_id);

        $assessments = $this->assessmentsForPeriod($period);
        $rows = $this->reportRows($assessments, $period);
        $stats = $this->reportStats($assessments);

        return view('kpi.report.preview', compact('period', 'rows', 'stats'))
            ->with('columns', self::REPORT_COLUMNS);
    }

    private function assessmentsForPeriod(KpiPeriod $period): EloquentCollection
    {
        // Eager load scores + skill untuk avoid N+1 query saat hitung Skor Self/Atasan
        return KpiAssessment::with(['user', 'atasan', 'period', 'scores.skill'])
            ->where('kpi_period_id', $period->id)
            ->whereIn('status', ['sudah_dicek', 'selesai'])
            ->orderBy(
                User::query()
                    ->select('nama')
                    ->whereColumn('users.id_user', 'kpi_assessments.user_id')
                    ->limit(1),
                'asc'
            )
            ->get();
    }

    private function reportRows(EloquentCollection $assessments, KpiPeriod $period): array
    {
        return $assessments->values()->map(function (KpiAssessment $assessment, int $index) use ($period) {
            $scores = $assessment->calculateSkillScores();

            return [
                'no' => $index + 1,
                'nik' => $assessment->user->nik ?? '-',
                'nama_karyawan' => $assessment->user->nama ?? '-',
                'divisi' => $assessment->user->divisi ?: '-',
                'jabatan' => $assessment->user->jabatan ?: '-',
                'periode' => $assessment->period->nama ?? $period->nama,
                'skor_soft_skill' => $this->formatScore($scores['soft_skill']),
                'skor_hard_skill' => $this->formatScore($scores['hard_skill']),
                'skor_self' => $this->formatScore($this->calculateSelfScore($assessment)),
                'skor_atasan' => $this->formatScore($this->calculateAtasanScore($assessment)),
                'skor_akhir' => $this->formatScore($assessment->skor_akhir),
                'grade' => $assessment->grade(),
                'status' => $assessment->status,
                'disetujui_oleh' => $assessment->atasan->nama ?? '-',
            ];
        })->all();
    }

    /**
     * Hitung Skor Self: rata-rata soft skill + weighted average hard skill
     * dari penilai_type='karyawan' saja.
     */
    private function calculateSelfScore(KpiAssessment $assessment): ?float
    {
        $softScores = $assessment->scores
            ->where('penilai_type', 'karyawan')
            ->where('skill_type', 'soft_skill');

        $hardScores = $assessment->scores
            ->where('penilai_type', 'karyawan')
            ->where('skill_type', 'hard_skill');

        $avgSoft = $softScores->count() > 0 ? $softScores->avg('skor') : null;
        $avgHard = $this->calculateWeightedAverageHardSkillSelf($hardScores);

        if ($avgSoft === null && $avgHard === null) {
            return null;
        }
        if ($avgSoft === null) {
            return $avgHard;
        }
        if ($avgHard === null) {
            return $avgSoft;
        }

        return ($avgSoft + $avgHard) / 2;
    }

    /**
     * Hitung Skor Atasan: rata-rata soft skill + weighted average hard skill
     * dari penilai_type='atasan' saja.
     */
    private function calculateAtasanScore(KpiAssessment $assessment): ?float
    {
        $softScores = $assessment->scores
            ->where('penilai_type', 'atasan')
            ->where('skill_type', 'soft_skill');

        $hardScores = $assessment->scores
            ->where('penilai_type', 'atasan')
            ->where('skill_type', 'hard_skill');

        $avgSoft = $softScores->count() > 0 ? $softScores->avg('skor') : null;
        $avgHard = $this->calculateWeightedAverageHardSkillSelf($hardScores);

        if ($avgSoft === null && $avgHard === null) {
            return null;
        }
        if ($avgSoft === null) {
            return $avgHard;
        }
        if ($avgHard === null) {
            return $avgSoft;
        }

        return ($avgSoft + $avgHard) / 2;
    }

    /**
     * Weighted average untuk hard skill dari koleksi skor yang sudah di-eager-load.
     * Weight diambil dari relasi hardSkill yang sudah di-load.
     */
    private function calculateWeightedAverageHardSkillSelf($scores): ?float
    {
        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($scores as $score) {
            // Pakai accessor hardSkillWeight (auto-resolve polymorphic)
            $weight = $score->hardSkillWeight;
            if ($weight === null || $weight <= 0) {
                continue;
            }
            $weightedSum += $score->skor * $weight;
            $totalWeight += $weight;
        }

        if ($totalWeight <= 0) {
            return null;
        }

        return $weightedSum / $totalWeight;
    }

    private function reportStats(EloquentCollection $assessments): array
    {
        return [
            'total' => $assessments->count(),
            'completed' => $assessments->where('status', 'selesai')->count(),
            'avg_score' => $assessments->whereNotNull('skor_akhir')->avg('skor_akhir'),
        ];
    }

    private function formatScore(int|float|string|null $score): string
    {
        return $score === null ? '-' : number_format((float) $score, 2, '.', '');
    }

    /**
     * Export ke CSV (ringan, tidak perlu library tambahan)
     */
    public function exportCsv(int $period)
    {
        $period = KpiPeriod::findOrFail($period);
        $rows = $this->reportRows($this->assessmentsForPeriod($period), $period);
        $csvContent = $this->generateCsvContent($rows);

        $filename = "KPI_Report_{$period->nama}_" . date('Ymd') . ".csv";

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Generate konten CSV
     */
    private function generateCsvContent(array $rows): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, array_values(self::REPORT_COLUMNS));
        foreach ($rows as $row) {
            fputcsv($stream, array_values($row));
        }
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return $content;
    }

    /**
     * Export ke Excel via HTML table (alternatif sederhana)
     */
    public function exportExcel(int $period)
    {
        $period = KpiPeriod::findOrFail($period);
        $rows = $this->reportRows($this->assessmentsForPeriod($period), $period);
        $html = $this->generateExcelHtml($rows);

        $filename = "KPI_Report_{$period->nama}_" . date('Ymd') . ".xls";

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Generate HTML untuk Excel export
     */
    private function generateExcelHtml(array $rows): string
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="utf-8"></head><body>';
        $html .= '<table border="1"><thead><tr>';
        foreach (self::REPORT_COLUMNS as $header) {
            $html .= '<th style="background:#9333ea;color:white;">' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return $html;
    }
}
