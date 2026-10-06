<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Material;
use App\Models\Odp;
use App\Models\Olt;
use App\Models\Ont;
use App\Models\Paket;
use App\Models\Pop;
use App\Models\PortPon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private const INDEX_ACCESS = [
        'area' => ['ADMIN'],
        'pop' => ['ADMIN'],
        'olt' => ['ADMIN', 'LEADER'],
        'odp' => ['ADMIN', 'LEADER'],
        'ont' => ['ADMIN', 'LOGISTIK', 'TEKNISI'],
        'port-pon' => ['ADMIN', 'TEKNISI'],
        'material' => ['ADMIN', 'LOGISTIK', 'TEKNISI'],
        'paket' => ['ADMIN', 'LOGISTIK'],
    ];

    private function createUser(string $role): User
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => $role, 'status' => true]);

        return $user;
    }

    private function createMasterDataFixtures(): array
    {
        $area = Area::create(['kode_area' => 'AR001', 'nama_area' => 'Area Test']);
        $pop = Pop::create([
            'kode_pop' => 'POP001', 'nama_pop' => 'POP Test', 'alamat' => 'Alamat Test',
            'latitude' => 0, 'longitude' => 0, 'id_area' => $area->id_area,
        ]);
        $olt = Olt::create([
            'kode_olt' => 'OLT001', 'nama_olt' => 'OLT Test', 'lokasi' => 'Lokasi Test',
            'latitude' => 0, 'longitude' => 0, 'id_pop' => $pop->id_pop,
        ]);
        $odp = Odp::create([
            'kode_odp' => 'ODP001', 'nama_odp' => 'ODP Test', 'alamat' => 'Alamat ODP',
            'latitude' => 0, 'longitude' => 0, 'jumlah_port' => 8, 'stok_port' => 8,
            'id_olt' => $olt->id_olt,
        ]);
        $ont = Ont::create([
            'serial_number' => 'ONT-TEST-001', 'pelanggan' => 'Pelanggan Test',
            'status' => 'TERSEDIA', 'id_pop' => $pop->id_pop, 'id_odp' => $odp->id_odp,
        ]);
        $port = PortPon::create([
            'nomor_port' => 1, 'tipe_kartu' => 'GPON', 'status' => 'TERSEDIA',
            'id_olt' => $olt->id_olt, 'id_odp' => $odp->id_odp,
        ]);
        $material = Material::create([
            'kode_material' => 'MAT0001', 'nama_material' => 'Material Test', 'stok' => 1,
            'minimal_stok' => 0, 'satuan' => 'pcs', 'harga' => 1000, 'kondisi' => 'BAIK',
        ]);
        $paket = Paket::create([
            'kode_paket' => 'PK001', 'nama_paket' => 'Paket Test', 'kecepatan' => '10 Mbps', 'harga' => 10000,
        ]);

        return compact('area', 'pop', 'olt', 'odp', 'ont', 'port', 'material', 'paket');
    }

    public function test_master_data_index_routes_match_the_five_role_matrix(): void
    {
        foreach (['ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK'] as $role) {
            $user = $this->createUser($role);

            foreach (self::INDEX_ACCESS as $resource => $allowedRoles) {
                $response = $this->actingAs($user)->get(route("masterdata.{$resource}.index"));

                if (in_array($role, $allowedRoles, true)) {
                    $response->assertOk();
                } else {
                    $response->assertForbidden();
                }
            }
        }
    }

    public function test_sidebar_master_data_links_are_visible_only_to_allowed_roles(): void
    {
        foreach (['ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK'] as $role) {
            $user = $this->createUser($role);
            $response = $this->actingAs($user)->get(route('profile.edit'));

            $response->assertOk();
            foreach (self::INDEX_ACCESS as $resource => $allowedRoles) {
                $url = route("masterdata.{$resource}.index");
                if (in_array($role, $allowedRoles, true)) {
                    $response->assertSee($url, false);
                } else {
                    $response->assertDontSee($url, false);
                }
            }

            if ($role === 'SALES') {
                $response->assertDontSeeText('Master Data');
            } else {
                $response->assertSeeText('Master Data');
            }
        }
    }

    public function test_users_sidebar_link_and_lainnya_heading_are_admin_only(): void
    {
        foreach (['ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK'] as $role) {
            $response = $this->actingAs($this->createUser($role))->get(route('profile.edit'));

            $response->assertOk();
            if ($role === 'ADMIN') {
                $response->assertSee(route('users.index'), false)
                    ->assertSeeText('Lainnya');
            } else {
                $response->assertDontSee(route('users.index'), false)
                    ->assertDontSeeText('Lainnya');
            }
        }
    }

    public function test_master_data_index_actions_match_role_permissions(): void
    {
        $fixtures = $this->createMasterDataFixtures();
        $resources = [
            'area' => ['id_area', 'area'],
            'pop' => ['id_pop', 'pop'],
            'olt' => ['id_olt', 'olt'],
            'odp' => ['id_odp', 'odp'],
            'ont' => ['id_ont', 'ont'],
            'port-pon' => ['id_port', 'port'],
            'material' => ['id_material', 'material'],
            'paket' => ['id_paket', 'paket'],
        ];

        foreach (self::INDEX_ACCESS as $resource => $allowedRoles) {
            if (!in_array('ADMIN', $allowedRoles, true)) {
                continue;
            }

            [$key, $fixtureKey] = $resources[$resource];
            $response = $this->actingAs($this->createUser('ADMIN'))
                ->get(route("masterdata.{$resource}.index"));
            $response->assertOk()
                ->assertSee(route("masterdata.{$resource}.create"), false)
                ->assertSee(route("masterdata.{$resource}.edit", $fixtures[$fixtureKey]->{$key}), false)
                ->assertSee('name="_method" value="DELETE"', false);
        }

        foreach ([
            'LEADER' => ['olt', 'odp'],
            'TEKNISI' => ['ont', 'port-pon', 'material'],
            'LOGISTIK' => ['paket'],
        ] as $role => $resourcesWithoutDelete) {
            foreach ($resourcesWithoutDelete as $resource) {
                [$key, $fixtureKey] = $resources[$resource];
                $response = $this->actingAs($this->createUser($role))
                    ->get(route("masterdata.{$resource}.index"));
                $response->assertOk()
                    ->assertSee(route("masterdata.{$resource}.create"), false)
                    ->assertSee(route("masterdata.{$resource}.edit", $fixtures[$fixtureKey]->{$key}), false)
                    ->assertDontSee('name="_method" value="DELETE"', false);
            }
        }

        $technician = $this->createUser('TEKNISI');
        $this->actingAs($technician)
            ->get(route('masterdata.material.index'))
            ->assertDontSee('title="Tambah Stok"', false);

        $logistik = $this->createUser('LOGISTIK');
        $materialResponse = $this->actingAs($logistik)->get(route('masterdata.material.index'));
        $materialResponse->assertSee('title="Tambah Stok"', false)
            ->assertSee('name="_method" value="DELETE"', false);
        $this->get(route('masterdata.ont.index'))
            ->assertSee('name="_method" value="DELETE"', false);
    }

    public function test_admin_can_delete_each_master_data_resource(): void
    {
        $user = $this->createUser('ADMIN');
        $fixtures = $this->createMasterDataFixtures();
        $deleteOrder = ['paket', 'material', 'ont', 'port', 'odp', 'olt', 'pop', 'area'];

        foreach ($deleteOrder as $resource) {
            $routeResource = $resource === 'port' ? 'port-pon' : $resource;
            $model = $fixtures[$resource];
            $parameter = $resource === 'area' ? $model->id_area
                : ($resource === 'pop' ? $model->id_pop
                : ($resource === 'olt' ? $model->id_olt
                : ($resource === 'odp' ? $model->id_odp
                : ($resource === 'ont' ? $model->id_ont
                : ($resource === 'port' ? $model->id_port
                : ($resource === 'material' ? $model->id_material : $model->id_paket))))));

            $this->actingAs($user)
                ->delete(route("masterdata.{$routeResource}.destroy", $parameter))
                ->assertRedirect(route("masterdata.{$routeResource}.index"));
        }
    }

    public function test_additional_roles_cannot_delete_resources_but_logistik_keeps_material_and_ont_delete(): void
    {
        $fixtures = $this->createMasterDataFixtures();
        $deniedDeletes = [
            'LEADER' => ['olt', 'odp'],
            'TEKNISI' => ['ont', 'port-pon', 'material'],
            'LOGISTIK' => ['paket'],
        ];

        foreach ($deniedDeletes as $role => $resources) {
            $user = $this->createUser($role);
            foreach ($resources as $resource) {
                $model = $fixtures[$resource === 'port-pon' ? 'port' : $resource];
                $parameter = match ($resource) {
                    'olt' => $model->id_olt,
                    'odp' => $model->id_odp,
                    'ont' => $model->id_ont,
                    'port-pon' => $model->id_port,
                    'material' => $model->id_material,
                    'paket' => $model->id_paket,
                };

                $this->actingAs($user)
                    ->delete(route("masterdata.{$resource}.destroy", $parameter))
                    ->assertForbidden();
            }
        }

        $logistik = $this->createUser('LOGISTIK');
        foreach (['material', 'ont'] as $resource) {
            $model = $fixtures[$resource];
            $parameter = $resource === 'material' ? $model->id_material : $model->id_ont;
            $this->actingAs($logistik)
                ->delete(route("masterdata.{$resource}.destroy", $parameter))
                ->assertRedirect(route("masterdata.{$resource}.index"));
        }
    }

    public function test_technician_cannot_add_material_stock(): void
    {
        $technician = $this->createUser('TEKNISI');
        $material = Material::create([
            'kode_material' => 'MAT-STOCK-01', 'nama_material' => 'Material Stok', 'stok' => 2,
            'minimal_stok' => 0, 'satuan' => 'pcs', 'harga' => 1000, 'kondisi' => 'BAIK',
        ]);

        $this->actingAs($technician)
            ->post(route('masterdata.material.addStock', $material->id_material), ['jumlah_tambah' => 1])
            ->assertForbidden();
    }
}