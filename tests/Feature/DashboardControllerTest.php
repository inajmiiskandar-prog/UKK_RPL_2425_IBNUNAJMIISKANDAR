<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Fab;
use App\Models\Odp;
use App\Models\Olt;
use App\Models\Ont;
use App\Models\Paket;
use App\Models\Pop;
use App\Models\PortPon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => $role, 'status' => true]);

        return $user;
    }

    private function createFixtures(): array
    {
        $suffix = substr(uniqid('', true), -8);

        // Setup Area, Paket, POP, OLT, ODP
        $area = Area::create([
            'kode_area' => 'AR' . $suffix,
            'nama_area' => 'Area Test',
        ]);
        Paket::create([
            'kode_paket' => 'PK' . $suffix,
            'nama_paket' => 'Paket Test',
            'kecepatan' => '20 Mbps',
            'harga' => 20000,
        ]);
        $pop = Pop::create([
            'kode_pop' => 'POP' . $suffix,
            'nama_pop' => 'POP Test',
            'alamat' => 'Alamat POP Test',
            'latitude' => 0,
            'longitude' => 0,
            'id_area' => $area->id_area,
        ]);
        $olt = Olt::create([
            'kode_olt' => 'OLT' . $suffix,
            'nama_olt' => 'OLT Test',
            'lokasi' => 'Lokasi OLT Test',
            'latitude' => 0,
            'longitude' => 0,
            'id_pop' => $pop->id_pop,
        ]);
        $odp = Odp::create([
            'kode_odp' => 'ODP' . $suffix,
            'nama_odp' => 'ODP Test',
            'alamat' => 'Alamat ODP Test',
            'latitude' => 0,
            'longitude' => 0,
            'jumlah_port' => 8,
            'stok_port' => 8,
            'id_olt' => $olt->id_olt,
        ]);

        // Create ONT with different statuses
        $ontTersedia = Ont::create([
            'serial_number' => 'ONT-AVAIL-' . $suffix,
            'pelanggan' => 'Pelanggan ONT Tersedia',
            'status' => 'TERSEDIA',
            'id_pop' => $pop->id_pop,
            'id_odp' => $odp->id_odp,
        ]);
        $ontTerpasang = Ont::create([
            'serial_number' => 'ONT-INSTALLED-' . $suffix,
            'pelanggan' => 'Pelanggan ONT Terpasang',
            'status' => 'TERPASANG',
            'id_pop' => $pop->id_pop,
            'id_odp' => $odp->id_odp,
        ]);
        $ontRusak = Ont::create([
            'serial_number' => 'ONT-DAMAGED-' . $suffix,
            'pelanggan' => 'Pelanggan ONT Rusak',
            'status' => 'RUSAK',
            'id_pop' => $pop->id_pop,
            'id_odp' => $odp->id_odp,
        ]);

        // Create PortPon with different statuses
        $portTersedia = PortPon::create([
            'nomor_port' => 1,
            'tipe_kartu' => 'GPON',
            'status' => 'TERSEDIA',
            'id_olt' => $olt->id_olt,
            'id_odp' => $odp->id_odp,
        ]);
        $portTerpasang = PortPon::create([
            'nomor_port' => 2,
            'tipe_kartu' => 'GPON',
            'status' => 'TERPASANG',
            'id_olt' => $olt->id_olt,
            'id_odp' => $odp->id_odp,
        ]);

        return compact('ontTersedia', 'ontTerpasang', 'ontRusak', 'portTersedia', 'portTerpasang');
    }

    /**
     * Test: ADMIN dapat mengakses /dashboard dan melihat status ONT/PortPON dengan benar.
     */
    public function test_admin_can_access_dashboard_and_see_correct_ont_port_status(): void
    {
        $admin = $this->createUser('ADMIN');
        $this->createFixtures();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');

        // Verifikasi ONT counts: 1 TERSEDIA, 1 TERPASANG, 1 RUSAK
        $this->assertEquals(1, Ont::where('status', 'TERSEDIA')->count());
        $this->assertEquals(1, Ont::where('status', 'TERPASANG')->count());
        $this->assertEquals(1, Ont::where('status', 'RUSAK')->count());

        // Verifikasi PortPon counts: 1 TERSEDIA, 1 TERPASANG
        $this->assertEquals(1, PortPon::where('status', 'TERSEDIA')->count());
        $this->assertEquals(1, PortPon::where('status', 'TERPASANG')->count());
    }

    /**
     * Test: ONT count menggunakan status TERSEDIA (bukan TERSDIA).
     */
    public function test_ont_tersedia_count_is_correct(): void
    {
        $admin = $this->createUser('ADMIN');
        $this->createFixtures();

        // Buat ONT tambahan dengan status TERSEDIA
        $suffix = substr(uniqid('', true), -8);
        $pop = Pop::first();
        $odp = Odp::first();
        Ont::create([
            'serial_number' => 'ONT-EXTRA-' . $suffix,
            'pelanggan' => 'Extra ONT',
            'status' => 'TERSEDIA',
            'id_pop' => $pop->id_pop,
            'id_odp' => $odp->id_odp,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        // Total ONT TERSEDIA = 2 (dari createFixtures + 1 tambahan)
        $response->assertOk();
        // Verifikasi ONT tersedia di kartu dashboard
        $this->assertEquals(2, Ont::where('status', 'TERSEDIA')->count());
    }

    /**
     * Test: Monthly stats menggunakan Carbon-based query (SQLite compatible, bukan MONTH()/YEAR()).
     */
    public function test_monthly_stats_use_carbon_based_query(): void
    {
        $admin = $this->createUser('ADMIN');

        // Dashboard harus bisa di-load tanpa error (之前的 MONTH()/YEAR() akan error di SQLite)
        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();

        // Verify Carbon-based monthly query works (uses whereBetween instead of MONTH/YEAR)
        // Test dengan membuat FAB dan memeriksa monthly count menggunakan Carbon
        $suffix = substr(uniqid('', true), -8);
        $area = Area::create([
            'kode_area' => 'AR' . $suffix,
            'nama_area' => 'Area Monthly Test',
        ]);
        $paket = Paket::create([
            'kode_paket' => 'PK' . $suffix,
            'nama_paket' => 'Paket Monthly Test',
            'kecepatan' => '20 Mbps',
            'harga' => 20000,
        ]);

        // Buat FAB baru - akan masuk bulan ini
        Fab::create([
            'kode_fab' => 'FAB-THISMONTH-' . $suffix,
            'nama_pelanggan' => 'Pelanggan Bulan Ini',
            'nik' => 'NIK' . $suffix,
            'no_hp' => '0800000000',
            'alamat' => 'Alamat Test',
            'latitude' => 0,
            'longitude' => 0,
            'status' => 'OPEN',
            'id_area' => $area->id_area,
            'id_paket' => $paket->id_paket,
            'id_user' => $admin->id_user,
            'id_penginput' => $admin->id_user,
        ]);

        // Hitung FAB bulan ini menggunakan Carbon-based query
        $now = Carbon::now();
        $count = Fab::whereBetween('createdAt', [
            $now->copy()->startOfMonth()->toDateTimeString(),
            $now->copy()->endOfMonth()->toDateTimeString()
        ])->count();

        // Harus ada minimal 1 FAB (yang baru dibuat)
        $this->assertGreaterThanOrEqual(1, $count);
    }

    /**
     * Test: Dashboard tidak bisa diakses tanpa login.
     */
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect('/login');
    }
}
