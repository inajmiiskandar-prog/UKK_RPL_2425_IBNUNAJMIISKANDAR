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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiReportController extends Controller
{
    /**
     * Halaman utama export
     */
    public function index(Request $request)
    {
        // Ambil periode untuk dropdown filter
        $periods = KpiPeriod::orderByDesc('tanggal_mulai')->get();

        // Periode yang dipilih
        $selectedPeriod = $request->period_id
            ? KpiPeriod::find($request->period_id)
            : KpiPeriod::where('status', 'selesai')->orWhere('status', 'aktif')->first();

        return view('kpi.report.index', compact('periods', 'selectedPeriod'));
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

        // Ambil semua assessment di periode ini
        $assessments = KpiAssessment::with(['user', 'atasan', 'scores'])
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

        // Statistik summary
        $stats = [
            'total' => $assessments->count(),
            'completed' => $assessments->where('status', 'selesai')->count(),
            'pending' => $assessments->where('status', 'pending')->count(),
            'self_done' => $assessments->where('status', 'self_done')->count(),
            'atasan_done' => $assessments->where('status', 'atasan_done')->count(),
            'avg_score' => $assessments->whereNotNull('skor_akhir')->avg('skor_akhir'),
            'min_score' => $assessments->whereNotNull('skor_akhir')->min('skor_akhir'),
            'max_score' => $assessments->whereNotNull('skor_akhir')->max('skor_akhir'),
        ];

        return view('kpi.report.preview', compact('period', 'assessments', 'stats'));
    }

    /**
     * Export ke CSV (ringan, tidak perlu library tambahan)
     */
    public function exportCsv(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:kpi_periods,id',
        ]);

        $period = KpiPeriod::findOrFail($request->period_id);
        $assessments = KpiAssessment::with(['user', 'atasan', 'scores'])
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

        // Generate CSV
        $csvContent = $this->generateCsvContent($period, $assessments);

        $filename = "KPI_Report_{$period->nama}_" . date('Ymd') . ".csv";

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Generate konten CSV
     */
    private function generateCsvContent($period, $assessments)
    {
        $lines = [];

        // Header
        $lines[] = "LAPORAN REKAPITULASI KPI";
        $lines[] = "Periode: {$period->nama}";
        $lines[] = "Tanggal: {$period->tanggal_mulai->format('d/m/Y')} - {$period->tanggal_selesai->format('d/m/Y')}";
        $lines[] = "";

        // Header tabel
        $lines[] = "No,NIK,Nama Karyawan,Divisi,Jabatan,NIK Atasan,Nama Atasan,Status Penilaian,Skor Akhir";

        // Data
        $no = 1;
        foreach ($assessments as $a) {
            $user = $a->user;
            $atasan = $a->atasan;

            $statusLabel = match($a->status) {
                'pending' => 'Belum Dinilai',
                'self_done' => 'Self Assessment Selesai',
                'atasan_done' => 'Penilaian Atasan Selesai',
                'selesai' => 'Selesai',
                default => $a->status,
            };

            $lines[] = implode(',', [
                $no++,
                $this->escapeCsv($user->nik ?? '-'),
                $this->escapeCsv($user->nama),
                $this->escapeCsv($user->divisi ?? '-'),
                $this->escapeCsv($user->jabatan ?? '-'),
                $this->escapeCsv($atasan->nik ?? '-'),
                $this->escapeCsv($atasan->nama ?? '-'),
                $statusLabel,
                $a->skor_akhir !== null ? number_format($a->skor_akhir, 2) : '-',
            ]);
        }

        // Footer statistics
        $lines[] = "";
        $lines[] = "RINGKASAN";
        $completed = $assessments->where('status', 'selesai');
        $lines[] = "Total Karyawan," . $assessments->count();
        $lines[] = "Sudah Selesai," . $completed->count();
        $lines[] = "Rata-rata Skor," . ($completed->avg('skor_akhir') !== null ? number_format($completed->avg('skor_akhir'), 2) : '-');
        $lines[] = "Skor Tertinggi," . ($completed->max('skor_akhir') !== null ? number_format($completed->max('skor_akhir'), 2) : '-');
        $lines[] = "Skor Terendah," . ($completed->min('skor_akhir') !== null ? number_format($completed->min('skor_akhir'), 2) : '-');

        return implode("\n", $lines);
    }

    /**
     * Escape karakter untuk CSV
     */
    private function escapeCsv($value)
    {
        if ($value === null || $value === '') {
            return '-';
        }
        $value = str_replace('"', '""', $value);
        if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
            return '"' . $value . '"';
        }
        return $value;
    }

    /**
     * Export ke Excel via HTML table (alternatif sederhana)
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:kpi_periods,id',
        ]);

        $period = KpiPeriod::findOrFail($request->period_id);
        $assessments = KpiAssessment::with(['user', 'atasan'])
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

        // Generate HTML table yang bisa di-save sebagai Excel
        $html = $this->generateExcelHtml($period, $assessments);

        $filename = "KPI_Report_{$period->nama}_" . date('Ymd') . ".xls";

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Generate HTML untuk Excel export
     */
    private function generateExcelHtml($period, $assessments)
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="utf-8"></head><body>';
        $html .= '<table border="1">';

        // Title
        $html .= '<tr><td colspan="9" style="font-weight:bold;font-size:14pt;">LAPORAN REKAPITULASI KPI</td></tr>';
        $html .= "<tr><td colspan=\"9\">Periode: {$period->nama} ({$period->tanggal_mulai->format('d/m/Y')} - {$period->tanggal_selesai->format('d/m/Y')})</td></tr>";
        $html .= '<tr></tr>';

        // Header
        $headers = ['No', 'NIK', 'Nama Karyawan', 'Divisi', 'Jabatan', 'Nama Atasan', 'Status', 'Skor Akhir'];
        $html .= '<tr>';
        foreach ($headers as $h) {
            $html .= "<th style=\"background:#9333ea;color:white;\">{$h}</th>";
        }
        $html .= '</tr>';

        // Data
        $no = 1;
        foreach ($assessments as $a) {
            $user = $a->user;
            $atasan = $a->atasan;

            $statusLabel = match($a->status) {
                'pending' => 'Belum Dinilai',
                'self_done' => 'Self Selesai',
                'atasan_done' => 'Atasan Selesai',
                'selesai' => 'Selesai',
                default => $a->status,
            };

            $bg = $no % 2 === 0 ? '#f3e8ff' : '#ffffff';

            $html .= "<tr style=\"background:{$bg}\">";
            $html .= "<td>{$no}</td>";
            $html .= "<td>{$user->nik}</td>";
            $html .= "<td>{$user->nama}</td>";
            $html .= "<td>{$user->divisi}</td>";
            $html .= "<td>{$user->jabatan}</td>";
            $html .= "<td>{$atasan->nama}</td>";
            $html .= "<td>{$statusLabel}</td>";
            $html .= "<td>" . ($a->skor_akhir !== null ? number_format($a->skor_akhir, 2) : '-') . "</td>";
            $html .= '</tr>';
            $no++;
        }

        $html .= '</table></body></html>';

        return $html;
    }
}
