<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Odp;
use App\Models\Olt;
use App\Models\Ont;
use App\Models\Pop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OdpExportTest extends TestCase
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
        ], $attributes));
    }

    private function createOdp(array $attributes = []): Odp
    {
        $olt = $attributes['olt'] ?? $this->createOlt();

        return Odp::create(array_merge([
            'kode_odp' => 'ODP001',
            'nama_odp' => 'ODP Test',
            'alamat' => 'Jl. ODP Test No. 1',
            'latitude' => -6.9,
            'longitude' => 107.6,
            'jumlah_port' => 16,
            'stok_port' => 16,
            'id_olt' => $olt->id_olt,
        ], $attributes));
    }

    private function createOnt(array $attributes = []): Ont
    {
        $odp = $attributes['odp'] ?? $this->createOdp();

        return Ont::create(array_merge([
            'serial_number' => 'SN' . rand(1000, 9999),
            'pelanggan' => 'Pelanggan Test',
            'status' => 'TERPASANG',
            'id_pop' => $odp->olt->id_pop,
            'id_odp' => $odp->id_odp,
        ], $attributes));
    }

    // =====================================================
    // Akses Role
    // =====================================================

    public function test_admin_can_export_odp_to_csv(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOdp();

        $response = $this->actingAs($admin)->get(route('masterdata.odp.exportCsv'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('attachment; filename="Export_ODP_', $response->headers->get('Content-Disposition'));
    }

    public function test_admin_can_export_odp_to_excel(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOdp();

        $response = $this->actingAs($admin)->get(route('masterdata.odp.exportExcel'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
        $this->assertStringContainsString('attachment; filename="Export_ODP_', $response->headers->get('Content-Disposition'));
    }

    public function test_leader_can_export_odp(): void
    {
        $leader = $this->createUser(['role' => 'LEADER']);
        $this->createOdp();

        $response = $this->actingAs($leader)->get(route('masterdata.odp.exportCsv'));

        $response->assertOk();
    }

    public function test_non_admin_leader_roles_cannot_access_export(): void
    {
        $urls = [
            route('masterdata.odp.exportCsv'),
            route('masterdata.odp.exportExcel'),
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
            route('masterdata.odp.exportCsv'),
            route('masterdata.odp.exportExcel'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    // =====================================================
    // Header dan Isi (14 kolom)
    // =====================================================

    public function test_csv_has_correct_headers(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOdp();

        $response = $this->actingAs($admin)->get(route('masterdata.odp.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $expectedHeaders = [
            'No', 'Kode ODP', 'Nama ODP', 'Alamat', 'OLT', 'POP', 'Area',
            'Jumlah Port', 'Port Terpakai', 'Port Tersedia', 'ONT Terpasang',
            'Latitude', 'Longitude', 'Tanggal Dibuat'
        ];
        $this->assertSame($expectedHeaders, str_getcsv($lines[0]));
    }

    public function test_excel_has_correct_headers(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOdp();

        $response = $this->actingAs($admin)->get(route('masterdata.odp.exportExcel'));
        $html = $response->getContent();

        preg_match_all('/<th[^>]*>(.*?)<\/th>/s', $html, $matches);
        $headers = array_map(fn ($h) => trim(html_entity_decode(strip_tags($h))), $matches[1]);

        $expectedHeaders = [
            'No', 'Kode ODP', 'Nama ODP', 'Alamat', 'OLT', 'POP', 'Area',
            'Jumlah Port', 'Port Terpakai', 'Port Tersedia', 'ONT Terpasang',
            'Latitude', 'Longitude', 'Tanggal Dibuat'
        ];
        $this->assertSame($expectedHeaders, $headers);
    }

    public function test_csv_contains_correct_data(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $area = $this->createArea(['kode_area' => 'AR999', 'nama_area' => 'Jakarta Selatan']);
        $pop = $this->createPop(['kode_pop' => 'POP999', 'nama_pop' => 'POP Jakarta', 'area' => $area]);
        $olt = $this->createOlt(['kode_olt' => 'OLT999', 'nama_olt' => 'OLT Jakarta', 'pop' => $pop]);
        $this->createOdp([
            'kode_odp' => 'ODP999',
            'nama_odp' => 'ODP Bandung',
            'alamat' => 'Jl. Merdeka No. 1',
            'latitude' => -6.9175,
            'longitude' => 107.6191,
            'jumlah_port' => 24,
            'olt' => $olt,
        ]);

        $response = $this->actingAs($admin)->get(route('masterdata.odp.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(2, $lines);
        $data = str_getcsv($lines[1]);
        $this->assertSame('1', $data[0]); // No
        $this->assertSame('ODP999', $data[1]); // Kode ODP
        $this->assertSame('ODP Bandung', $data[2]); // Nama ODP
        $this->assertSame('Jl. Merdeka No. 1', $data[3]); // Alamat
        $this->assertSame('OLT Jakarta', $data[4]); // OLT
        $this->assertSame('POP Jakarta', $data[5]); // POP
        $this->assertSame('Jakarta Selatan', $data[6]); // Area
        $this->assertSame('24', $data[7]); // Jumlah Port
        $this->assertSame('0', $data[8]); // Port Terpakai (0 ONT)
        $this->assertSame('24', $data[9]); // Port Tersedia (24 - 0)
        $this->assertSame('0', $data[10]); // ONT Terpasang
        $this->assertSame('-6.9175', $data[11]); // Latitude
        $this->assertSame('107.6191', $data[12]); // Longitude
    }

    public function test_excel_contains_correct_data(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOdp(['kode_odp' => 'ODP888', 'nama_odp' => 'ODP Surabaya']);

        $response = $this->actingAs($admin)->get(route('masterdata.odp.exportExcel'));
        $html = $response->getContent();

        $this->assertStringContainsString('ODP888', $html);
        $this->assertStringContainsString('ODP Surabaya', $html);
    }

    // =====================================================
    // Hitungan Port dan ONT
    // =====================================================

    public function test_port_counts_are_correct_with_onts(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $odp = $this->createOdp(['jumlah_port' => 16]);
        $this->createOnt(['odp' => $odp]);
        $this->createOnt(['odp' => $odp]);
        $this->createOnt(['odp' => $odp]);

        $response = $this->actingAs($admin)->get(route('masterdata.odp.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));
        $data = str_getcsv($lines[1]);

        $this->assertSame('16', $data[7]); // Jumlah Port
        $this->assertSame('3', $data[8]); // Port Terpakai (3 ONT)
        $this->assertSame('13', $data[9]); // Port Tersedia (16 - 3)
        $this->assertSame('3', $data[10]); // ONT Terpasang
    }

    public function test_port_tersedia_never_negative(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $odp = $this->createOdp(['jumlah_port' => 2]);
        $this->createOnt(['odp' => $odp]);
        $this->createOnt(['odp' => $odp]);
        $this->createOnt(['odp' => $odp]);
        $this->createOnt(['odp' => $odp]);
        $this->createOnt(['odp' => $odp]);

        $response = $this->actingAs($admin)->get(route('masterdata.odp.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));
        $data = str_getcsv($lines[1]);

        // Port Tersedia tidak boleh negatif
        $this->assertSame('0', $data[9]);
    }

    public function test_multiple_odps_with_different_ont_counts(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $area = $this->createArea();
        $pop = $this->createPop(['area' => $area]);
        $olt = $this->createOlt(['pop' => $pop]);

        $odp1 = Odp::create([
            'kode_odp' => 'ODP001', 'nama_odp' => 'ODP Satu',
            'alamat' => 'Alamat 1', 'latitude' => 0, 'longitude' => 0,
            'jumlah_port' => 8, 'stok_port' => 8, 'id_olt' => $olt->id_olt,
        ]);
        $odp2 = Odp::create([
            'kode_odp' => 'ODP002', 'nama_odp' => 'ODP Dua',
            'alamat' => 'Alamat 2', 'latitude' => 0, 'longitude' => 0,
            'jumlah_port' => 16, 'stok_port' => 16, 'id_olt' => $olt->id_olt,
        ]);

        // ODP1: 2 ONT, ODP2: 5 ONT
        $this->createOnt(['odp' => $odp1]);
        $this->createOnt(['odp' => $odp1]);
        $this->createOnt(['odp' => $odp2]);
        $this->createOnt(['odp' => $odp2]);
        $this->createOnt(['odp' => $odp2]);
        $this->createOnt(['odp' => $odp2]);
        $this->createOnt(['odp' => $odp2]);

        $response = $this->actingAs($admin)->get(route('masterdata.odp.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(3, $lines); // 1 header + 2 data

        $row1 = str_getcsv($lines[1]);
        $row2 = str_getcsv($lines[2]);

        // ODP1: 8 port, 2 terpakai, 6 tersedia
        $this->assertSame('ODP001', $row1[1]);
        $this->assertSame('8', $row1[7]);
        $this->assertSame('2', $row1[8]);
        $this->assertSame('6', $row1[9]);
        $this->assertSame('2', $row1[10]);

        // ODP2: 16 port, 5 terpakai, 11 tersedia
        $this->assertSame('ODP002', $row2[1]);
        $this->assertSame('16', $row2[7]);
        $this->assertSame('5', $row2[8]);
        $this->assertSame('11', $row2[9]);
        $this->assertSame('5', $row2[10]);
    }

    // =====================================================
    // Filter Search dan OLT
    // =====================================================

    public function test_export_respects_search_filter(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $olt = $this->createOlt();
        $this->createOdp(['kode_odp' => 'ODP001', 'nama_odp' => 'ODP Jakarta', 'olt' => $olt]);
        $this->createOdp(['kode_odp' => 'ODP002', 'nama_odp' => 'ODP Bandung', 'olt' => $olt]);

        $response = $this->actingAs($admin)->get(
            route('masterdata.odp.exportCsv', ['search' => 'Jakarta'])
        );
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(2, $lines); // 1 header + 1 data
        $this->assertStringContainsString('ODP Jakarta', $response->getContent());
        $dataLines = implode(' ', array_slice($lines, 1));
        $this->assertStringNotContainsString('ODP Bandung', $dataLines);
    }

    public function test_export_respects_olt_filter(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $area = $this->createArea();
        $pop = $this->createPop(['area' => $area]);
        $olt1 = $this->createOlt(['kode_olt' => 'OLT001', 'nama_olt' => 'OLT Satu', 'pop' => $pop]);
        $olt2 = $this->createOlt(['kode_olt' => 'OLT002', 'nama_olt' => 'OLT Dua', 'pop' => $pop]);
        $this->createOdp(['kode_odp' => 'ODP001', 'nama_odp' => 'ODP Satu', 'olt' => $olt1]);
        $this->createOdp(['kode_odp' => 'ODP002', 'nama_odp' => 'ODP Dua', 'olt' => $olt2]);

        $response = $this->actingAs($admin)->get(
            route('masterdata.odp.exportCsv', ['olt' => $olt1->id_olt])
        );
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(2, $lines); // 1 header + 1 data
        $this->assertStringContainsString('ODP Satu', $response->getContent());
        $dataLines = implode(' ', array_slice($lines, 1));
        $this->assertStringNotContainsString('ODP Dua', $dataLines);
    }

    // =====================================================
    // Route Dynamic Segment Prevention
    // =====================================================

    public function test_export_route_is_not_caught_by_dynamic_odp_segment(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->get(route('masterdata.odp.exportCsv'));
        $response->assertOk(); // Bukan 404
    }

    public function test_export_csv_route_is_before_show_route(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $odp = $this->createOdp();

        $exportResponse = $this->actingAs($admin)->get(route('masterdata.odp.exportCsv'));
        $exportResponse->assertOk();

        $showResponse = $this->actingAs($admin)->get(route('masterdata.odp.show', $odp->id_odp));
        $showResponse->assertOk();
    }

    // =====================================================
    // Empty Data
    // =====================================================

    public function test_export_empty_odp_returns_only_headers(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->get(route('masterdata.odp.exportCsv'));
        $lines = array_values(array_filter(explode("\n", trim($response->getContent()))));

        $this->assertCount(1, $lines);
        $expectedHeaders = [
            'No', 'Kode ODP', 'Nama ODP', 'Alamat', 'OLT', 'POP', 'Area',
            'Jumlah Port', 'Port Terpakai', 'Port Tersedia', 'ONT Terpasang',
            'Latitude', 'Longitude', 'Tanggal Dibuat'
        ];
        $this->assertSame($expectedHeaders, str_getcsv($lines[0]));
    }

    // =====================================================
    // Kolom Tanggal Dibuat di Index
    // =====================================================

    public function test_index_shows_tanggal_dibuat_column(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOdp();

        $response = $this->actingAs($admin)->get(route('masterdata.odp.index'));

        $response->assertOk();
        $response->assertSee('Tanggal Dibuat');
        $response->assertSee('ODP Test');
    }

    // =====================================================
    // Latitude Negatif (tanpa awalan ')
    // =====================================================

    public function test_negative_latitude_without_prefix(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN']);
        $this->createOdp(['latitude' => -6.9175, 'longitude' => 107.6191]);

        $response = $this->actingAs($admin)->get(route('masterdata.odp.exportCsv'));
        $content = $response->getContent();

        $this->assertStringContainsString('-6.9175', $content);
        $this->assertStringNotContainsString("'-6.9175", $content);
    }
}
