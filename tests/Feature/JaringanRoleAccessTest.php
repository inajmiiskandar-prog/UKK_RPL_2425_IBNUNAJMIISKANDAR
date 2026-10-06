<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Baa;
use App\Models\Fab;
use App\Models\Odp;
use App\Models\Olt;
use App\Models\Ont;
use App\Models\Paket;
use App\Models\Pop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JaringanRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => $role, 'status' => true]);

        return $user;
    }

    private function createFabFixture(User $user, bool $withBaa = false): array
    {
        $suffix = substr(uniqid('', true), -8);
        $area = Area::create(['kode_area' => 'AR' . $suffix, 'nama_area' => 'Area Jaringan']);
        $paket = Paket::create([
            'kode_paket' => 'PK' . $suffix, 'nama_paket' => 'Paket Jaringan',
            'kecepatan' => '20 Mbps', 'harga' => 20000,
        ]);
        $fab = Fab::create([
            'kode_fab' => 'FAB' . $suffix, 'nama_pelanggan' => 'Pelanggan Jaringan',
            'nik' => 'NIK' . $suffix, 'no_hp' => '0800000000', 'alamat' => 'Alamat Jaringan',
            'latitude' => 0, 'longitude' => 0, 'status' => 'OPEN',
            'id_area' => $area->id_area, 'id_paket' => $paket->id_paket,
            'id_user' => $user->id_user, 'id_penginput' => $user->id_user,
        ]);

        $baa = null;
        if ($withBaa) {
            $pop = Pop::create([
                'kode_pop' => 'POP' . $suffix, 'nama_pop' => 'POP Jaringan', 'alamat' => 'Alamat POP',
                'latitude' => 0, 'longitude' => 0, 'id_area' => $area->id_area,
            ]);
            $olt = Olt::create([
                'kode_olt' => 'OLT' . $suffix, 'nama_olt' => 'OLT Jaringan', 'lokasi' => 'Lokasi OLT',
                'latitude' => 0, 'longitude' => 0, 'id_pop' => $pop->id_pop,
            ]);
            $odp = Odp::create([
                'kode_odp' => 'ODP' . $suffix, 'nama_odp' => 'ODP Jaringan', 'alamat' => 'Alamat ODP',
                'latitude' => 0, 'longitude' => 0, 'jumlah_port' => 8, 'stok_port' => 8,
                'id_olt' => $olt->id_olt,
            ]);
            $ont = Ont::create([
                'serial_number' => 'ONT' . $suffix, 'pelanggan' => 'Pelanggan Jaringan',
                'status' => 'TERSEDIA', 'id_pop' => $pop->id_pop, 'id_odp' => $odp->id_odp,
            ]);
            $baa = Baa::create([
                'kode_baa' => 'BAA' . $suffix, 'tanggal_instalasi' => '2026-01-01 10:00:00',
                'status' => 'PENDING', 'id_fab' => $fab->id_fab, 'id_user' => $user->id_user,
                'id_olt' => $olt->id_olt, 'id_ont' => $ont->id_ont, 'id_odp' => $odp->id_odp,
                'port_olt' => 1,
            ]);
        }

        return compact('fab', 'baa');
    }

    public function test_fab_and_baa_index_routes_match_the_five_role_matrix(): void
    {
        $allowedRoles = [
            'fab' => ['ADMIN', 'LEADER', 'SALES', 'TEKNISI'],
            'baa' => ['ADMIN', 'LEADER', 'TEKNISI', 'SALES'],
        ];

        foreach (['ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK'] as $role) {
            $user = $this->createUser($role);
            foreach ($allowedRoles as $resource => $roles) {
                $response = $this->actingAs($user)->get(route("jaringan.{$resource}.index"));
                if (in_array($role, $roles, true)) {
                    $response->assertOk();
                } else {
                    $response->assertForbidden();
                }
            }
        }
    }

    public function test_sidebar_settings_fab_baa_and_transaksi_heading_match_roles(): void
    {
        $menuRoles = [
            'settings' => ['ADMIN'],
            'fab' => ['ADMIN', 'LEADER', 'SALES', 'TEKNISI'],
            'baa' => ['ADMIN', 'LEADER', 'TEKNISI', 'SALES'],
        ];

        foreach (['ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK'] as $role) {
            $response = $this->actingAs($this->createUser($role))->get(route('profile.edit'));
            $response->assertOk();

            foreach ($menuRoles as $menu => $roles) {
                $url = match ($menu) {
                    'settings' => route('settings.index'),
                    'fab' => route('jaringan.fab.index'),
                    'baa' => route('jaringan.baa.index'),
                };
                if (in_array($role, $roles, true)) {
                    $response->assertSee($url, false);
                } else {
                    $response->assertDontSee($url, false);
                }
            }

            if ($role === 'LOGISTIK') {
                $response->assertDontSeeText('Transaksi');
            } else {
                $response->assertSeeText('Transaksi');
            }
        }
    }

    public function test_fab_and_baa_destroy_are_admin_only(): void
    {
        $fixtures = $this->createFabFixture($this->createUser('ADMIN'), true);
        $additionalRoles = ['LEADER', 'SALES', 'TEKNISI', 'LOGISTIK'];

        foreach ($additionalRoles as $role) {
            $user = $this->createUser($role);
            $this->actingAs($user)
                ->delete(route('jaringan.fab.destroy', $fixtures['fab']->id_fab))
                ->assertForbidden();
            $this->delete(route('jaringan.baa.destroy', $fixtures['baa']->id_baa))
                ->assertForbidden();
        }

        $admin = $this->createUser('ADMIN');
        $fabFixture = $this->createFabFixture($admin);
        $this->actingAs($admin)
            ->delete(route('jaringan.fab.destroy', $fabFixture['fab']->id_fab))
            ->assertRedirect(route('jaringan.fab.index'));

        $baaResponse = $this->delete(route('jaringan.baa.destroy', $fixtures['baa']->id_baa));
        $this->assertNotEquals(403, $baaResponse->status());
    }
}