<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Olt;
use App\Models\Pop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OltExportTest extends TestCase
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

    private function createOlt(array $attributes = []): Olt
    {
        $pop = $attributes['pop'] ?? $this->createPop();

        return Olt::create(array_merge([
            'kode_olt' => 'OLT001',
            'nama_olt' => 'OLT Test',
            'lokasi' => 'Lokasi Test',
            'latitude' => -6.9,
            'longitude' => 107.6,
            'id_pop' => $pop->id_pop,
            'ip_olt' => '192.168.1.1',
            'username_olt' => 'admin',
            'password_olt' => 'secret123',
        ], $attributes));
    }

    // =====================================================
    // Akses Role
    // =====================================================

    public function test_admin_can_export_olt_to_csv(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOlt();

        $response = $this->actingAs($admin)->get(route('masterdata.olt.exportCsv'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('attachment; filename="Export_OLT_', $response->headers->get('Content-Disposition'));
    }

    public function test_admin_can_export_olt_to_excel(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOlt();

        $response = $this->actingAs($admin)->get(route('masterdata.olt.exportExcel'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
        $this->assertStringContainsString('attachment; filename="Export_OLT_', $response->headers->get('Content-Disposition'));
    }

    public function test_leader_can_export_olt(): void
    {
        $leader = $this->createUser(['role' => 'LEADER']);
        $this->createOlt();

        $response = $this->actingAs($leader)->get(route('masterdata.olt.exportCsv'));

        $response->assertOk();
    }

    public function test_non_admin_leader_roles_cannot_access_export(): void
    {
        $urls = [
            route('masterdata.olt.exportCsv'),
            route('masterdata.olt.exportExcel'),
        ];

        foreach (['SALES', 'TEKNISI', 'LOGISTIK'] as $role) {
            $user = $this->createUser(['role' => $role]);

            foreach ($urls as $url) {
                $this->actingAs($user)->get($url)->assertForbidden();
            }
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $urls = [
            route('masterdata.olt.exportCsv'),
            route('masterdata.olt.exportExcel'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    // =====================================================
    // Header dan Isi (10 kolom, TANPA username & password)
    // =====================================================

    public function test_csv_has_correct_headers(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOlt();

        $response = $this->actingAs($admin)->get(route('masterdata.olt.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $expectedHeaders = ['No', 'Kode OLT', 'Nama OLT', 'Lokasi', 'IP OLT', 'POP', 'Area', 'Latitude', 'Longitude', 'Tanggal Dibuat'];
        $this->assertSame($expectedHeaders, str_getcsv($lines[0]));
    }

    public function test_excel_has_correct_headers(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOlt();

        $response = $this->actingAs($admin)->get(route('masterdata.olt.exportExcel'));
        $html = $response->getContent();

        preg_match_all('/<th[^>]*>(.*?)<\/th>/s', $html, $matches);
        $headers = array_map(fn ($h) => trim(html_entity_decode(strip_tags($h))), $matches[1]);

        $expectedHeaders = ['No', 'Kode OLT', 'Nama OLT', 'Lokasi', 'IP OLT', 'POP', 'Area', 'Latitude', 'Longitude', 'Tanggal Dibuat'];
        $this->assertSame($expectedHeaders, $headers);
    }

    public function test_csv_contains_correct_data(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $area = $this->createArea(['kode_area' => 'AR999', 'nama_area' => 'Jakarta Selatan']);
        $pop = $this->createPop(['kode_pop' => 'POP999', 'nama_pop' => 'POP Jakarta', 'area' => $area]);
        $this->createOlt([
            'kode_olt' => 'OLT999',
            'nama_olt' => 'OLT Bandung',
            'lokasi' => 'Gedung A',
            'ip_olt' => '10.0.0.1',
            'latitude' => -6.9175,
            'longitude' => 107.6191,
            'pop' => $pop,
        ]);

        $response = $this->actingAs($admin)->get(route('masterdata.olt.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(2, $lines);
        $data = str_getcsv($lines[1]);
        $this->assertSame('1', $data[0]); // No
        $this->assertSame('OLT999', $data[1]); // Kode OLT
        $this->assertSame('OLT Bandung', $data[2]); // Nama OLT
        $this->assertSame('Gedung A', $data[3]); // Lokasi
        $this->assertSame('10.0.0.1', $data[4]); // IP OLT
        $this->assertSame('POP Jakarta', $data[5]); // POP
        $this->assertSame('Jakarta Selatan', $data[6]); // Area
        $this->assertSame('-6.9175', $data[7]); // Latitude
        $this->assertSame('107.6191', $data[8]); // Longitude
    }

    public function test_excel_contains_correct_data(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOlt(['kode_olt' => 'OLT888', 'nama_olt' => 'OLT Surabaya']);

        $response = $this->actingAs($admin)->get(route('masterdata.olt.exportExcel'));
        $html = $response->getContent();

        $this->assertStringContainsString('OLT888', $html);
        $this->assertStringContainsString('OLT Surabaya', $html);
    }

    public function test_username_and_password_not_in_export(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOlt([
            'username_olt' => 'admin_secret',
            'password_olt' => 'password_123',
        ]);

        $csvResponse = $this->actingAs($admin)->get(route('masterdata.olt.exportCsv'));
        $csvContent = $csvResponse->getContent();

        $this->assertStringNotContainsString('admin_secret', $csvContent);
        $this->assertStringNotContainsString('password_123', $csvContent);
        $this->assertStringNotContainsString('Username', $csvContent);
        $this->assertStringNotContainsString('Password', $csvContent);

        $xlsResponse = $this->actingAs($admin)->get(route('masterdata.olt.exportExcel'));
        $xlsContent = $xlsResponse->getContent();

        $this->assertStringNotContainsString('admin_secret', $xlsContent);
        $this->assertStringNotContainsString('password_123', $xlsContent);
    }

    public function test_negative_latitude_without_prefix(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOlt(['latitude' => -6.9175, 'longitude' => 107.6191]);

        $response = $this->actingAs($admin)->get(route('masterdata.olt.exportCsv'));
        $content = $response->getContent();

        $this->assertStringContainsString('-6.9175', $content);
        $this->assertStringNotContainsString("'-6.9175", $content);
    }

    public function test_multiple_olts_are_exported_in_order(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        Area::create(['kode_area' => 'AR001', 'nama_area' => 'Area Satu']);
        Pop::create(['kode_pop' => 'POP001', 'nama_pop' => 'POP Satu', 'alamat' => 'Alamat 1', 'latitude' => 0, 'longitude' => 0, 'id_area' => 1]);
        Olt::create(['kode_olt' => 'OLT001', 'nama_olt' => 'OLT Satu', 'lokasi' => 'Lok 1', 'latitude' => 0, 'longitude' => 0, 'id_pop' => 1, 'ip_olt' => '1.1.1.1']);
        Olt::create(['kode_olt' => 'OLT002', 'nama_olt' => 'OLT Dua', 'lokasi' => 'Lok 2', 'latitude' => 0, 'longitude' => 0, 'id_pop' => 1, 'ip_olt' => '2.2.2.2']);
        Olt::create(['kode_olt' => 'OLT003', 'nama_olt' => 'OLT Tiga', 'lokasi' => 'Lok 3', 'latitude' => 0, 'longitude' => 0, 'id_pop' => 1, 'ip_olt' => '3.3.3.3']);

        $response = $this->actingAs($admin)->get(route('masterdata.olt.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(4, $lines); // 1 header + 3 data

        $row1 = str_getcsv($lines[1]);
        $row2 = str_getcsv($lines[2]);
        $row3 = str_getcsv($lines[3]);

        $this->assertSame('OLT001', $row1[1]);
        $this->assertSame('OLT002', $row2[1]);
        $this->assertSame('OLT003', $row3[1]);
    }

    // =====================================================
    // Filter Search dan POP
    // =====================================================

    public function test_export_respects_search_filter(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $pop = $this->createPop();
        $this->createOlt(['kode_olt' => 'OLT001', 'nama_olt' => 'OLT Jakarta', 'pop' => $pop]);
        $this->createOlt(['kode_olt' => 'OLT002', 'nama_olt' => 'OLT Bandung', 'pop' => $pop]);

        $response = $this->actingAs($admin)->get(
            route('masterdata.olt.exportCsv', ['search' => 'Jakarta'])
        );
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(2, $lines); // 1 header + 1 data
        $this->assertStringContainsString('OLT Jakarta', $response->getContent());
        $dataLines = implode(' ', array_slice($lines, 1));
        $this->assertStringNotContainsString('OLT Bandung', $dataLines);
    }

    public function test_export_respects_pop_filter(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $area = $this->createArea();
        $pop1 = $this->createPop(['kode_pop' => 'POP001', 'nama_pop' => 'POP Satu', 'area' => $area]);
        $pop2 = $this->createPop(['kode_pop' => 'POP002', 'nama_pop' => 'POP Dua', 'area' => $area]);
        $this->createOlt(['kode_olt' => 'OLT001', 'nama_olt' => 'OLT Satu', 'pop' => $pop1]);
        $this->createOlt(['kode_olt' => 'OLT002', 'nama_olt' => 'OLT Dua', 'pop' => $pop2]);

        $response = $this->actingAs($admin)->get(
            route('masterdata.olt.exportCsv', ['pop' => $pop1->id_pop])
        );
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(2, $lines); // 1 header + 1 data
        $this->assertStringContainsString('OLT Satu', $response->getContent());
        $dataLines = implode(' ', array_slice($lines, 1));
        $this->assertStringNotContainsString('OLT Dua', $dataLines);
    }

    // =====================================================
    // Route Dynamic Segment Prevention
    // =====================================================

    public function test_export_route_is_not_caught_by_dynamic_olt_segment(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->get(route('masterdata.olt.exportCsv'));
        $response->assertOk(); // Bukan 404
    }

    public function test_export_csv_route_is_before_show_route(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $olt = $this->createOlt();

        $exportResponse = $this->actingAs($admin)->get(route('masterdata.olt.exportCsv'));
        $exportResponse->assertOk();

        $showResponse = $this->actingAs($admin)->get(route('masterdata.olt.show', $olt->id_olt));
        $showResponse->assertOk();
    }

    // =====================================================
    // Empty Data
    // =====================================================

    public function test_export_empty_olt_returns_only_headers(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->get(route('masterdata.olt.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(1, $lines);
        $this->assertSame(['No', 'Kode OLT', 'Nama OLT', 'Lokasi', 'IP OLT', 'POP', 'Area', 'Latitude', 'Longitude', 'Tanggal Dibuat'], str_getcsv($lines[0]));
    }

    // =====================================================
    // Kolom Tanggal Dibuat di Index
    // =====================================================

    public function test_index_shows_tanggal_dibuat_column(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOlt();

        $response = $this->actingAs($admin)->get(route('masterdata.olt.index'));

        $response->assertOk();
        $response->assertSee('Tanggal Dibuat');
        $response->assertSee('OLT Test');
    }
}
