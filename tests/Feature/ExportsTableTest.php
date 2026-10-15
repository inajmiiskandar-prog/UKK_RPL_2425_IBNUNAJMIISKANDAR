<?php

namespace Tests\Feature;

use App\Http\Controllers\Controller;
use Tests\TestCase;

/**
 * Test trait ExportsTable secara langsung (tanpa database).
 * Note: method protected/private diuji via ReflectionMethod.
 * Method wrapper dibuat public di anonymous class untuk pengujian integration.
 */
class ExportsTableTest extends TestCase
{
    private object $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Anonymous class dengan wrapper public untuk method protected trait
        $this->controller = new class extends Controller {
            use \App\Traits\ExportsTable;

            // Wrapper public untuk pengujian
            public function testToCsvContent(array $headers, array $rows): string
            {
                return $this->toCsvContent($headers, $rows);
            }

            public function testToXlsContent(array $headers, array $rows): string
            {
                return $this->toXlsContent($headers, $rows);
            }

            public function testGenerateExportFilename(string $prefix, string $extension): string
            {
                return $this->generateExportFilename($prefix, $extension);
            }

            public function testSanitizeCellValue(mixed $value): mixed
            {
                return $this->sanitizeCellValue($value);
            }
        };
    }

    // =====================================================
    // Proteksi CSV Injection (via ReflectionMethod)
    // =====================================================

    public function test_string_starting_with_equals_sign_is_protected(): void
    {
        $method = new \ReflectionMethod($this->controller, 'testSanitizeCellValue');
        $method->setAccessible(true);

        // = diawali formula → beri awalan '
        $this->assertSame("'=test", $method->invoke($this->controller, '=test'));
        $this->assertSame("'=SUM(A1:A10)", $method->invoke($this->controller, '=SUM(A1:A10)'));
    }

    public function test_string_starting_with_plus_sign_is_protected(): void
    {
        $method = new \ReflectionMethod($this->controller, 'testSanitizeCellValue');
        $method->setAccessible(true);

        $this->assertSame("'+data", $method->invoke($this->controller, '+data'));
    }

    public function test_string_starting_with_at_sign_is_protected(): void
    {
        $method = new \ReflectionMethod($this->controller, 'testSanitizeCellValue');
        $method->setAccessible(true);

        $this->assertSame("'@email", $method->invoke($this->controller, '@email'));
    }

    public function test_string_starting_with_tab_is_protected(): void
    {
        $method = new \ReflectionMethod($this->controller, 'testSanitizeCellValue');
        $method->setAccessible(true);

        $this->assertSame("'\ttabbed", $method->invoke($this->controller, "\ttabbed"));
    }

    public function test_negative_numbers_are_not_protected(): void
    {
        $method = new \ReflectionMethod($this->controller, 'testSanitizeCellValue');
        $method->setAccessible(true);

        // Input string '-6.9175' TIDAK di-escape, tetap string (karena is_numeric)
        // Trait menerima mixed input; untuk string numerik, return apa adanya
        $this->assertSame('-6.9175', $method->invoke($this->controller, '-6.9175'));
        $this->assertSame('-100', $method->invoke($this->controller, '-100'));
    }

    public function test_positive_numbers_are_not_protected(): void
    {
        $method = new \ReflectionMethod($this->controller, 'testSanitizeCellValue');
        $method->setAccessible(true);

        // Input string numerik TIDAK di-escape, tetap string
        $this->assertSame('5.5', $method->invoke($this->controller, '5.5'));
        $this->assertSame('100', $method->invoke($this->controller, '100'));
    }

    public function test_dash_alone_is_treated_as_empty(): void
    {
        $method = new \ReflectionMethod($this->controller, 'testSanitizeCellValue');
        $method->setAccessible(true);

        // "-" adalah placeholder kosong, tampilkan kosong
        $this->assertSame('', $method->invoke($this->controller, '-'));
    }

    public function test_empty_string_is_preserved_as_empty(): void
    {
        $method = new \ReflectionMethod($this->controller, 'testSanitizeCellValue');
        $method->setAccessible(true);

        $this->assertSame('', $method->invoke($this->controller, ''));
    }

    public function test_regular_strings_are_not_modified(): void
    {
        $method = new \ReflectionMethod($this->controller, 'testSanitizeCellValue');
        $method->setAccessible(true);

        $this->assertSame('Jakarta Selatan', $method->invoke($this->controller, 'Jakarta Selatan'));
        $this->assertSame('Area-test', $method->invoke($this->controller, 'Area-test'));
        $this->assertSame('Data (with parens)', $method->invoke($this->controller, 'Data (with parens)'));
    }

    public function test_string_starting_with_carriage_return_is_protected(): void
    {
        $method = new \ReflectionMethod($this->controller, 'testSanitizeCellValue');
        $method->setAccessible(true);

        // \r diawali formula → beri awalan '
        $this->assertSame("'\rhidden", $method->invoke($this->controller, "\rhidden"));
    }

    // =====================================================
    // CSV Content (via wrapper public)
    // =====================================================

    public function test_to_csv_content_includes_headers(): void
    {
        $headers = ['No', 'Nama'];
        $rows = [
            ['no' => 1, 'nama' => 'Area Satu'],
        ];

        $csv = $this->controller->testToCsvContent($headers, $rows);
        $lines = array_values(array_filter(explode("\n", trim($csv))));

        $this->assertCount(2, $lines);
        $this->assertSame(['No', 'Nama'], str_getcsv($lines[0]));
    }

    public function test_to_csv_content_protects_injection_in_data(): void
    {
        $headers = ['Kode', 'Nama'];
        $rows = [
            ['kode' => '=HACK()', 'nama' => 'Normal'],
            ['kode' => 'AR001', 'nama' => '+bonus'],
        ];

        $csv = $this->controller->testToCsvContent($headers, $rows);
        $lines = array_values(array_filter(explode("\n", trim($csv))));

        // Baris 1: kode '=HACK()' harus dimulai dengan ' (proteksi formula)
        $this->assertStringStartsWith("'", str_getcsv($lines[1])[0]);
        // Baris 1: nama 'Normal' TIDAK perlu dilindungi (regular string)
        $this->assertSame('Normal', str_getcsv($lines[1])[1]);
        // Baris 2: kode 'AR001' TIDAK perlu dilindungi (regular string)
        $this->assertSame('AR001', str_getcsv($lines[2])[0]);
        // Baris 2: nama '+bonus' harus dimulai dengan ' (proteksi formula)
        $this->assertStringStartsWith("'", str_getcsv($lines[2])[1]);
    }

    // =====================================================
    // XLS Content (via wrapper public)
    // =====================================================

    public function test_to_xls_content_includes_headers(): void
    {
        $headers = ['No', 'Nama'];
        $rows = [
            ['no' => 1, 'nama' => 'Area Satu'],
        ];

        $xls = $this->controller->testToXlsContent($headers, $rows);

        $this->assertStringContainsString('<th', $xls);
        $this->assertStringContainsString('No', $xls);
        $this->assertStringContainsString('Nama', $xls);
    }

    public function test_to_xls_content_applies_htmlspecialchars(): void
    {
        $headers = ['Nama'];
        $rows = [
            ['nama' => '<script>alert(1)</script>'],
        ];

        $xls = $this->controller->testToXlsContent($headers, $rows);

        // Karakter HTML harus di-escape
        $this->assertStringContainsString('&lt;script&gt;', $xls);
        $this->assertStringNotContainsString('<script>', $xls);
    }

    public function test_to_xls_content_displays_dash_for_empty_values(): void
    {
        $headers = ['Nama', 'Keterangan'];
        $rows = [
            ['nama' => 'Area Satu', 'keterangan' => ''],
        ];

        $xls = $this->controller->testToXlsContent($headers, $rows);

        // Nilai kosong harus tampil sebagai "-"
        $this->assertStringContainsString('>-</td>', $xls);
    }

    public function test_to_xls_content_has_purple_header_style(): void
    {
        $headers = ['Kolom'];
        $rows = [['kolom' => 'data']];

        $xls = $this->controller->testToXlsContent($headers, $rows);

        // Header styling purple (konsisten dengan KPI export)
        $this->assertStringContainsString('background:#9333ea', $xls);
        $this->assertStringContainsString('color:white', $xls);
    }

    public function test_to_xls_content_protects_formula_injection(): void
    {
        $headers = ['Kode', 'Nama'];
        $rows = [
            ['kode' => '=HYPERLINK("http://x")', 'nama' => 'Normal'],
            ['kode' => 'AR001', 'nama' => '+bonus'],
            ['kode' => 'AR002', 'nama' => '@email'],
        ];

        $xls = $this->controller->testToXlsContent($headers, $rows);

        // =HYPERLINK dilindungi dengan awalan ' (htmlspecialchars escape ' ke &#039;)
        $this->assertStringContainsString('&#039;=HYPERLINK', $xls);
        // +bonus dilindungi dengan awalan ' (htmlspecialchars ENT_QUOTES escape ' ke &#039;)
        $this->assertStringContainsString('&#039;+bonus', $xls);
        // @email dilindungi
        $this->assertStringContainsString('&#039;@email', $xls);
        // Regular string tidak berubah
        $this->assertStringContainsString('>Normal<', $xls);
        $this->assertStringContainsString('>AR001<', $xls);
    }

    public function test_to_xls_content_preserves_numbers_and_empty(): void
    {
        $headers = ['Nama', 'Nilai'];
        $rows = [
            ['nama' => 'Negatif', 'nilai' => '-6.9175'],
            ['nama' => 'Positif', 'nilai' => '5.5'],
            ['nama' => 'Kosong', 'nilai' => ''],
        ];

        $xls = $this->controller->testToXlsContent($headers, $rows);

        // Angka negatif tidak berubah (tanpa awalan ')
        $this->assertStringContainsString('>-6.9175<', $xls);
        // Angka positif tidak berubah
        $this->assertStringContainsString('>5.5<', $xls);
        // Nilai kosong tampil sebagai "-"
        $this->assertStringContainsString('>-</td>', $xls);
    }

    // =====================================================
    // Filename Generation (via wrapper public)
    // =====================================================

    public function test_generate_export_filename_format(): void
    {
        $csvFilename = $this->controller->testGenerateExportFilename('Area', 'csv');
        $xlsFilename = $this->controller->testGenerateExportFilename('Area', 'xls');

        $this->assertStringStartsWith('Export_Area_', $csvFilename);
        $this->assertStringEndsWith('.csv', $csvFilename);
        $this->assertStringStartsWith('Export_Area_', $xlsFilename);
        $this->assertStringEndsWith('.xls', $xlsFilename);

        // Format tanggal: Y-m-d (contoh: 2026-01-15)
        $this->assertMatchesRegularExpression('/Export_Area_\d{4}-\d{2}-\d{2}\.(csv|xls)/', $csvFilename);
    }
}
