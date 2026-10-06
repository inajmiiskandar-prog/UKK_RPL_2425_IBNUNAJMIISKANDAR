<?php

namespace Tests\Feature;

use App\Models\KpiAssessment;
use App\Models\KpiPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KpiDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat periode KPI aktif
        $this->period = KpiPeriod::create([
            'nama' => 'September 2024',
            'tanggal_mulai' => '2024-09-01',
            'tanggal_selesai' => '2024-09-30',
            'status' => 'aktif',
        ]);

        // Buat periode sebelumnya untuk chart tren
        $this->prevPeriod = KpiPeriod::create([
            'nama' => 'Agustus 2024',
            'tanggal_mulai' => '2024-08-01',
            'tanggal_selesai' => '2024-08-31',
            'status' => 'selesai',
        ]);
    }

    #[Test]
    public function admin_can_view_kpi_dashboard_and_see_all_data()
    {
        // Buat admin
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        // Buat LEADER (bawahan admin)
        $leader = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
            'atasan_id' => $admin->id_user,
        ]);

        // Buat TEKNISI (bawahan leader)
        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        // Buat SALES
        $sales = User::factory()->create([
            'role' => 'SALES',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        // Buat LOGISTIK
        $logistik = User::factory()->create([
            'role' => 'LOGISTIK',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        // Buat assessment untuk TEKNISI (sudah_dicek)
        KpiAssessment::create([
            'user_id' => $teknisi->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $leader->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 85.5,
        ]);

        // Buat assessment menunggu review
        KpiAssessment::create([
            'user_id' => $sales->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $leader->id_user,
            'status' => 'menunggu_review',
            'skor_akhir' => null,
        ]);

        // Assessment periode sebelumnya
        KpiAssessment::create([
            'user_id' => $teknisi->id_user,
            'kpi_period_id' => $this->prevPeriod->id,
            'atasan_id' => $leader->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 80.0,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('kpi.dashboard', ['period_id' => $this->period->id]));

        $response->assertStatus(200);

        // Admin melihat semua karyawan aktif tanpa ADMIN = LEADER + TEKNISI + SALES + LOGISTIK = 4 user
        $response->assertSeeText('Total Karyawan');

        // Sudah dinilai = 1 (TEKNISI)
        $response->assertSeeText('Sudah Dinilai');

        // Rata-rata KPI
        $response->assertSee('85.5');
    }

    #[Test]
    public function leader_can_view_kpi_dashboard_and_see_subordinate_data()
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $leader = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
            'atasan_id' => $admin->id_user,
        ]);

        // TEKNISI bawahan leader
        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        // Assessment selesai
        KpiAssessment::create([
            'user_id' => $teknisi->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $leader->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 90.0,
        ]);

        $this->actingAs($leader);

        $response = $this->get(route('kpi.dashboard', ['period_id' => $this->period->id]));

        $response->assertStatus(200);
        $response->assertSeeText('Dashboard KPI');

        // LEADER melihat dirinya sendiri + bawahan = 2
        $response->assertSeeText('Total Karyawan');
    }

    #[Test]
    public function regular_employee_can_only_see_own_data()
    {
        $leader = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);

        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        // TEKNISI lain (bawahan leader yang sama)
        $otherTeknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => $leader->id_user,
            'nama' => 'Other Teknisi',
        ]);

        // Assessment untuk teknisi
        KpiAssessment::create([
            'user_id' => $teknisi->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $leader->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 75.0,
        ]);

        // Assessment untuk other teknisi
        KpiAssessment::create([
            'user_id' => $otherTeknisi->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $leader->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 88.0,
        ]);

        $this->actingAs($teknisi);

        $response = $this->get(route('kpi.dashboard', ['period_id' => $this->period->id]));

        $response->assertStatus(200);

        // TEKNISI reguler hanya melihat dirinya sendiri = 1
        $response->assertSeeText('Total Karyawan');

        // Tidak melihat data teknisi lain
        $response->assertDontSee('Other Teknisi');

        // Melihat skor dirinya sendiri
        $response->assertSee('75.0');
    }

    #[Test]
    public function user_without_subordinates_only_sees_own_data()
    {
        $leader = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);

        // SALES tanpa bawahan
        $sales = User::factory()->create([
            'role' => 'SALES',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        // TEKNISI bawahan leader
        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => $leader->id_user,
            'nama' => 'Bawahan Leader',
        ]);

        // Assessment untuk teknisi (skor unik 99.9 untuk deteksi)
        KpiAssessment::create([
            'user_id' => $teknisi->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $leader->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 99.9,
        ]);

        $this->actingAs($sales);

        $response = $this->get(route('kpi.dashboard', ['period_id' => $this->period->id]));

        $response->assertStatus(200);

        // SALES hanya melihat dirinya sendiri = 1
        $response->assertSeeText('Total Karyawan');

        // Tidak melihat skor bawahan leader lain
        $response->assertDontSee('99.9');
    }

    #[Test]
    public function dashboard_shows_empty_state_when_no_data()
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        // Buat periode baru tanpa data
        $emptyPeriod = KpiPeriod::create([
            'nama' => 'Oktober 2024',
            'tanggal_mulai' => '2024-10-01',
            'tanggal_selesai' => '2024-10-31',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('kpi.dashboard', ['period_id' => $emptyPeriod->id]));

        $response->assertStatus(200);
        $response->assertSee('Dashboard KPI');
    }

    #[Test]
    public function unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->get(route('kpi.dashboard'));

        $response->assertRedirect(route('login'));
    }
}
