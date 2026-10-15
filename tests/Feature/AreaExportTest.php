<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreaExportTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $attributes = []): User
    {
        /** @var User $user */
        $user = User::factory()->create(array_merge([
            'role' => 'ADMIN',
            'status' => true,
        ], $attributes));

        return $user;
    }

    private function createArea(array $attributes = []): Area
    {
        return Area::create(array_merge([
            'kode_area' => 'AR001',
            'nama_area' => 'Area Test',
            'keterangan' => 'Deskripsi test',
        ], $attributes));
    }

    // =====================================================
    // Akses Role
    // =====================================================

    public function test_admin_can_export_area_to_csv(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createArea(['kode_area' => 'AR001', 'nama_area' => 'Jakarta']);

        $response = $this->actingAs($admin)->get(route('masterdata.area.exportCsv'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('attachment; filename="Export_Area_', $response->headers->get('Content-Disposition'));
    }

    public function test_admin_can_export_area_to_excel(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createArea(['kode_area' => 'AR001', 'nama_area' => 'Jakarta']);

        $response = $this->actingAs($admin)->get(route('masterdata.area.exportExcel'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
        $this->assertStringContainsString('attachment; filename="Export_Area_', $response->headers->get('Content-Disposition'));
    }

    public function test_non_admin_roles_cannot_access_export(): void
    {
        $urls = [
            route('masterdata.area.exportCsv'),
            route('masterdata.area.exportExcel'),
        ];

        foreach (['LEADER', 'SALES', 'TEKNISI', 'LOGISTIK'] as $role) {
            $user = $this->createUser(['role' => $role]);

            foreach ($urls as $url) {
                $this->actingAs($user)->get($url)->assertForbidden();
            }
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $urls = [
            route('masterdata.area.exportCsv'),
            route('masterdata.area.exportExcel'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    // =====================================================
    // Header dan Isi
    // =====================================================

    public function test_csv_has_correct_headers(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createArea();

        $response = $this->actingAs($admin)->get(route('masterdata.area.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $expectedHeaders = ['No', 'Kode Area', 'Nama Area', 'Keterangan', 'Tanggal Dibuat'];
        $this->assertSame($expectedHeaders, str_getcsv($lines[0]));
    }

    public function test_excel_has_correct_headers(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createArea();

        $response = $this->actingAs($admin)->get(route('masterdata.area.exportExcel'));
        $html = $response->getContent();

        // Ekstrak header dari <th>
        preg_match_all('/<th[^>]*>(.*?)<\/th>/s', $html, $matches);
        $headers = array_map(fn ($h) => trim(html_entity_decode(strip_tags($h))), $matches[1]);

        $expectedHeaders = ['No', 'Kode Area', 'Nama Area', 'Keterangan', 'Tanggal Dibuat'];
        $this->assertSame($expectedHeaders, $headers);
    }

    public function test_csv_contains_correct_data(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createArea([
            'kode_area' => 'AR999',
            'nama_area' => 'Bandung',
            'keterangan' => 'Kota kembang',
        ]);

        $response = $this->actingAs($admin)->get(route('masterdata.area.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        // Baris data (baris 2)
        $this->assertCount(2, $lines);
        $data = str_getcsv($lines[1]);
        $this->assertSame('1', $data[0]); // No
        $this->assertSame('AR999', $data[1]); // Kode Area
        $this->assertSame('Bandung', $data[2]); // Nama Area
        $this->assertSame('Kota kembang', $data[3]); // Keterangan
    }

    public function test_excel_contains_correct_data(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createArea([
            'kode_area' => 'AR888',
            'nama_area' => 'Surabaya',
            'keterangan' => 'Kota pahlawan',
        ]);

        $response = $this->actingAs($admin)->get(route('masterdata.area.exportExcel'));
        $html = $response->getContent();

        $this->assertStringContainsString('AR888', $html);
        $this->assertStringContainsString('Surabaya', $html);
        $this->assertStringContainsString('Kota pahlawan', $html);
    }

    public function test_multiple_areas_are_exported_in_order(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        Area::create(['kode_area' => 'AR001', 'nama_area' => 'Area Satu']);
        Area::create(['kode_area' => 'AR002', 'nama_area' => 'Area Dua']);
        Area::create(['kode_area' => 'AR003', 'nama_area' => 'Area Tiga']);

        $response = $this->actingAs($admin)->get(route('masterdata.area.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(4, $lines); // 1 header + 3 data

        // Urutan berdasarkan kode_area ASC
        $row1 = str_getcsv($lines[1]);
        $row2 = str_getcsv($lines[2]);
        $row3 = str_getcsv($lines[3]);

        $this->assertSame('Area Satu', $row1[2]);
        $this->assertSame('Area Dua', $row2[2]);
        $this->assertSame('Area Tiga', $row3[2]);
    }

    // =====================================================
    // Filter Search
    // =====================================================

    public function test_export_respects_search_filter(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        Area::create(['kode_area' => 'AR001', 'nama_area' => 'Jakarta Selatan']);
        Area::create(['kode_area' => 'AR002', 'nama_area' => 'Bandung']);
        Area::create(['kode_area' => 'AR003', 'nama_area' => 'Jakarta Utara']);

        $response = $this->actingAs($admin)->get(
            route('masterdata.area.exportCsv', ['search' => 'Jakarta'])
        );
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        // Harus ada 1 header + 2 data (Jakarta Selatan, Jakarta Utara)
        $this->assertCount(3, $lines);
        $this->assertStringContainsString('Jakarta Selatan', $response->getContent());
        $this->assertStringContainsString('Jakarta Utara', $response->getContent());
        $this->assertStringNotContainsString('Bandung', implode(' ', array_slice($lines, 1)));
    }

    public function test_export_with_no_matching_search_returns_header_only(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        Area::create(['kode_area' => 'AR001', 'nama_area' => 'Jakarta']);

        $response = $this->actingAs($admin)->get(
            route('masterdata.area.exportCsv', ['search' => 'TidakAda'])
        );

        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));
        $this->assertCount(1, $lines); // Hanya header
    }

    // =====================================================
    // Route Dynamic Segment Prevention
    // =====================================================

    public function test_export_route_is_not_caught_by_dynamic_area_segment(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);

        // Route export TIDAK boleh tertangkap sebagai {area} dan return 404
        $response = $this->actingAs($admin)->get(route('masterdata.area.exportCsv'));
        $response->assertOk(); // Bukan 404
    }

    public function test_export_csv_route_is_before_show_route(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $area = $this->createArea();

        // Export CSV harus accessible dan TIDAK konflik dengan route {area}
        $exportResponse = $this->actingAs($admin)->get(route('masterdata.area.exportCsv'));
        $exportResponse->assertOk();

        // Show area normal juga harus accessible
        $showResponse = $this->actingAs($admin)->get(route('masterdata.area.show', $area->id_area));
        $showResponse->assertOk();
    }

    // =====================================================
    // Empty Data
    // =====================================================

    public function test_export_empty_area_returns_only_headers(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->get(route('masterdata.area.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        // Harus ada header tapi tidak ada data
        $this->assertCount(1, $lines);
        $this->assertSame(['No', 'Kode Area', 'Nama Area', 'Keterangan', 'Tanggal Dibuat'], str_getcsv($lines[0]));
    }
}
