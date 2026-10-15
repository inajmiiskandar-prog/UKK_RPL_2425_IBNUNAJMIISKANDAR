<?php

namespace App\Traits;

use Illuminate\Http\Response;

/**
 * Trait untuk export data tabel ke CSV dan XLS (HTML table format).
 * Tanpa library tambahan, kompatibel dengan Laravel 12.
 */
trait ExportsTable
{
    /**
     * Proteksi CSV/Formula injection.
     * Nilai string berawalan = + - @ TAB CR diberi awalan '
     * KECUALI: nilai numerik (is_numeric) dan "-" yang berarti kosong.
     */
    protected function sanitizeCellValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        // Abaikan jika kosong atau "-" (placeholder kosong)
        if ($value === '' || $value === '-') {
            return '';
        }

        // KECUALIKAN: angka (termasuk negatif seperti -6.9175)
        if (is_numeric($value)) {
            return $value;
        }

        // Proteksi formula injection: awalan = + - @ TAB CR
        if (preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Generate CSV string dengan proteksi injection.
     *
     * @param array $headers Array judul kolom
     * @param array $rows Array associative array [col => value, ...]
     */
    protected function toCsvContent(array $headers, array $rows): string
    {
        $stream = fopen('php://temp', 'r+');

        // Header
        fputcsv($stream, $headers);

        // Data rows dengan proteksi injection
        foreach ($rows as $row) {
            $safeRow = array_map([$this, 'sanitizeCellValue'], array_values($row));
            fputcsv($stream, $safeRow);
        }

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return $content;
    }

    /**
     * Generate XLS (HTML table) string.
     * Nilai kosong ditampilkan sebagai "-" untuk keterbacaan.
     *
     * @param array $headers Array judul kolom
     * @param array $rows Array associative array [col => value, ...]
     */
    protected function toXlsContent(array $headers, array $rows): string
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" ';
        $html .= 'xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="utf-8"></head><body>';
        $html .= '<table border="1"><thead><tr>';

        // Header dengan styling purple (konsisten dengan KPI export)
        foreach ($headers as $header) {
            $html .= '<th style="background:#9333ea;color:white;">';
            $html .= htmlspecialchars($header, ENT_QUOTES, 'UTF-8');
            $html .= '</th>';
        }
        $html .= '</tr></thead><tbody>';

        // Data rows: sanitize dulu untuk proteksi formula, lalu escape HTML
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach (array_values($row) as $value) {
                // 1. Proteksi formula injection (= + - @ TAB CR)
                $safeValue = $this->sanitizeCellValue($value);
                // 2. Escape HTML tags
                $safeValue = htmlspecialchars((string) $safeValue, ENT_QUOTES, 'UTF-8');
                // 3. Tampilkan "-" untuk nilai kosong
                if ($safeValue === '') {
                    $safeValue = '-';
                }
                $html .= '<td>' . $safeValue . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return $html;
    }

    /**
     * Generate nama file export standar.
     */
    protected function generateExportFilename(string $prefix, string $extension): string
    {
        $timestamp = now()->format('Y-m-d');
        return "Export_{$prefix}_{$timestamp}.{$extension}";
    }

    /**
     * Download response untuk CSV.
     */
    protected function csvResponse(string $content, string $filename): Response
    {
        return response($content)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Download response untuk XLS (HTML table).
     */
    protected function xlsResponse(string $content, string $filename): Response
    {
        return response($content)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
