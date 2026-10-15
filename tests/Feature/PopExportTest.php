<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Pop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopExportTest extends TestCase
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

    private function createPop(array $attributes = []): Pop
    {
        $area = $attributes['area'] ?? $this->createArea();

        return Pop::create(array_merge([
            'kode_pop' => 'POP001',
            'nama_pop' => 'POP Test',
            'alamat' => 'Jl. Test No. 1',
            'latitude' => -6.9175,
            'longitude' => 107.6191,
            'id_area' => $area->id_area,
        ], $attributes));
    }

    // =====================================================
    // Akses Role
    // =====================================================

    public function test_admin_can_export_pop_to_csv(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createPop();

        $response = $this->actingAs($admin)->get(route('masterdata.pop.exportCsv'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('attachment; filename="Export_POP_', $response->headers->get('Content-Disposition'));
    }

    public function test_admin_can_export_pop_to_excel(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createPop();

        $response = $this->actingAs($admin)->get(route('masterdata.pop.exportExcel'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
        $this->assertStringContainsString('attachment; filename="Export_POP_', $response->headers->get('Content-Disposition'));
    }

    public function test_non_admin_roles_cannot_access_export(): void
    {
        $urls = [
            route('masterdata.pop.exportCsv'),
            route('masterdata.pop.exportExcel'),
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
            route('masterdata.pop.exportCsv'),
            route('masterdata.pop.exportExcel'),
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
        $this->createPop();

        $response = $this->actingAs($admin)->get(route('masterdata.pop.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $expectedHeaders = ['No', 'Kode POP', 'Nama POP', 'Alamat', 'Area', 'Latitude', 'Longitude', 'Tanggal Dibuat'];
        $this->assertSame($expectedHeaders, str_getcsv($lines[0]));
    }

    public function test_excel_has_correct_headers(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createPop();

        $response = $this->actingAs($admin)->get(route('masterdata.pop.exportExcel'));
        $html = $response->getContent();

        // Ekstrak header dari <th>
        preg_match_all('/<th[^>]*>(.*?)<\/th>/s', $html, $matches);
        $headers = array_map(fn ($h) => trim(html_entity_decode(strip_tags($h))), $matches[1]);

        $expectedHeaders = ['No', 'Kode POP', 'Nama POP', 'Alamat', 'Area', 'Latitude', 'Longitude', 'Tanggal Dibuat'];
        $this->assertSame($expectedHeaders, $headers);
    }

    public function test_csv_contains_correct_data(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $area = $this->createArea(['kode_area' => 'AR999', 'nama_area' => 'Jakarta Selatan']);
        $this->createPop([
            'kode_pop' => 'POP999',
            'nama_pop' => 'POP Bandung',
            'alamat' => 'Jl. Asia Afrika',
            'latitude' => -6.9175,
            'longitude' => 107.6191,
            'area' => $area,
        ]);

        $response = $this->actingAs($admin)->get(route('masterdata.pop.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(2, $lines);
        $data = str_getcsv($lines[1]);
        $this->assertSame('1', $data[0]); // No
        $this->assertSame('POP999', $data[1]); // Kode POP
        $this->assertSame('POP Bandung', $data[2]); // Nama POP
        $this->assertSame('Jl. Asia Afrika', $data[3]); // Alamat
        $this->assertSame('Jakarta Selatan', $data[4]); // Area
        $this->assertSame('-6.9175', $data[5]); // Latitude (negatif tanpa awalan)
        $this->assertSame('107.6191', $data[6]); // Longitude
    }

    public function test_excel_contains_correct_data(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $area = $this->createArea(['kode_area' => 'AR888', 'nama_area' => 'Surabaya']);
        $this->createPop([
            'kode_pop' => 'POP888',
            'nama_pop' => 'POP Surabaya',
            'alamat' => 'Jl. Pemuda',
            'area' => $area,
        ]);

        $response = $this->actingAs($admin)->get(route('masterdata.pop.exportExcel'));
        $html = $response->getContent();

        $this->assertStringContainsString('POP888', $html);
        $this->assertStringContainsString('POP Surabaya', $html);
        $this->assertStringContainsString('Surabaya', $html);
    }

    public function test_negative_latitude_without_prefix(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createPop([
            'latitude' => -6.9175,
            'longitude' => 107.6191,
        ]);

        $response = $this->actingAs($admin)->get(route('masterdata.pop.exportCsv'));
        $content = $response->getContent();

        // Latitude negatif harus tanpa awalan '
        $this->assertStringContainsString('-6.9175', $content);
        $this->assertStringNotContainsString("'-6.9175", $content);
    }

    public function test_multiple_pops_are_exported_in_order(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        Area::create(['kode_area' => 'AR001', 'nama_area' => 'Area Satu']);
        Area::create(['kode_area' => 'AR002', 'nama_area' => 'Area Dua']);
        Pop::create(['kode_pop' => 'POP001', 'nama_pop' => 'POP Satu', 'alamat' => 'Alamat 1', 'latitude' => 0, 'longitude' => 0, 'id_area' => 1]);
        Pop::create(['kode_pop' => 'POP002', 'nama_pop' => 'POP Dua', 'alamat' => 'Alamat 2', 'latitude' => 0, 'longitude' => 0, 'id_area' => 2]);
        Pop::create(['kode_pop' => 'POP003', 'nama_pop' => 'POP Tiga', 'alamat' => 'Alamat 3', 'latitude' => 0, 'longitude' => 0, 'id_area' => 1]);

        $response = $this->actingAs($admin)->get(route('masterdata.pop.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(4, $lines); // 1 header + 3 data

        // Urutan berdasarkan kode_pop ASC
        $row1 = str_getcsv($lines[1]);
        $row2 = str_getcsv($lines[2]);
        $row3 = str_getcsv($lines[3]);

        $this->assertSame('POP001', $row1[1]);
        $this->assertSame('POP002', $row2[1]);
        $this->assertSame('POP003', $row3[1]);
    }

    // =====================================================
    // Filter Search dan Area
    // =====================================================

    public function test_export_respects_search_filter(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $area1 = $this->createArea(['kode_area' => 'AR001', 'nama_area' => 'Jakarta']);
        $area2 = $this->createArea(['kode_area' => 'AR002', 'nama_area' => 'Bandung']);
        $this->createPop(['kode_pop' => 'POP001', 'nama_pop' => 'POP Jakarta', 'alamat' => 'Alamat 1', 'area' => $area1]);
        $this->createPop(['kode_pop' => 'POP002', 'nama_pop' => 'POP Bandung', 'alamat' => 'Alamat 2', 'area' => $area2]);
        $this->createPop(['kode_pop' => 'POP003', 'nama_pop' => 'POP Jakarta Utara', 'alamat' => 'Alamat 3', 'area' => $area1]);

        $response = $this->actingAs($admin)->get(
            route('masterdata.pop.exportCsv', ['search' => 'Jakarta'])
        );
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        // Harus ada 1 header + 2 data (POP Jakarta, POP Jakarta Utara)
        $this->assertCount(3, $lines);
        $this->assertStringContainsString('POP Jakarta', $response->getContent());
        $this->assertStringContainsString('POP Jakarta Utara', $response->getContent());
        $dataLines = implode(' ', array_slice($lines, 1));
        $this->assertStringNotContainsString('POP Bandung', $dataLines);
    }

    public function test_export_respects_area_filter(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $area1 = $this->createArea(['kode_area' => 'AR001', 'nama_area' => 'Jakarta']);
        $area2 = $this->createArea(['kode_area' => 'AR002', 'nama_area' => 'Bandung']);
        $this->createPop(['kode_pop' => 'POP001', 'nama_pop' => 'POP Jakarta', 'alamat' => 'Alamat 1', 'area' => $area1]);
        $this->createPop(['kode_pop' => 'POP002', 'nama_pop' => 'POP Bandung', 'alamat' => 'Alamat 2', 'area' => $area2]);

        $response = $this->actingAs($admin)->get(
            route('masterdata.pop.exportCsv', ['area' => $area1->id_area])
        );
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(2, $lines); // 1 header + 1 data
        $this->assertStringContainsString('POP Jakarta', $response->getContent());
        $dataLines = implode(' ', array_slice($lines, 1));
        $this->assertStringNotContainsString('POP Bandung', $dataLines);
    }

    public function test_export_with_no_matching_search_returns_header_only(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createPop(['kode_pop' => 'POP001', 'nama_pop' => 'POP Jakarta']);

        $response = $this->actingAs($admin)->get(
            route('masterdata.pop.exportCsv', ['search' => 'TidakAda'])
        );

        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));
        $this->assertCount(1, $lines); // Hanya header
    }

    // =====================================================
    // Route Dynamic Segment Prevention
    // =====================================================

    public function test_export_route_is_not_caught_by_dynamic_pop_segment(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);

        // Route export TIDAK boleh tertangkap sebagai {pop} dan return 404
        $response = $this->actingAs($admin)->get(route('masterdata.pop.exportCsv'));
        $response->assertOk(); // Bukan 404
    }

    public function test_export_csv_route_is_before_show_route(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $pop = $this->createPop();

        // Export CSV harus accessible dan TIDAK konflik dengan route {pop}
        $exportResponse = $this->actingAs($admin)->get(route('masterdata.pop.exportCsv'));
        $exportResponse->assertOk();

        // Show POP normal juga harus accessible
        $showResponse = $this->actingAs($admin)->get(route('masterdata.pop.show', $pop->id_pop));
        $showResponse->assertOk();
    }

    // =====================================================
    // Empty Data
    // =====================================================

    public function test_export_empty_pop_returns_only_headers(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->get(route('masterdata.pop.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(1, $lines);
        $this->assertSame(['No', 'Kode POP', 'Nama POP', 'Alamat', 'Area', 'Latitude', 'Longitude', 'Tanggal Dibuat'], str_getcsv($lines[0]));
    }

    // =====================================================
    // Kolom Tanggal Dibuat di Index
    // =====================================================

    public function test_index_shows_tanggal_dibuat_column(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createPop();

        $response = $this->actingAs($admin)->get(route('masterdata.pop.index'));

        $response->assertOk();
        $response->assertSee('Tanggal Dibuat');
        $response->assertSee('POP Test');
    }
}
