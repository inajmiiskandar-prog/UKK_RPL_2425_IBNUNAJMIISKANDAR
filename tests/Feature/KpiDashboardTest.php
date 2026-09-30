<?php

namespace Tests\Feature;

use App\Models\KpiAssessment;
use App\Models\KpiPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    /** @test */
    public function admin_can_view_kpi_dashboard_and_see_all_data()
    {
        // Buat admin
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        // Buat HR
        $hr = User::factory()->create([
            'role' => 'HR',
            'status' => true,
            'atasan_id' => $admin->id_user,
        ]);

        // Buat karyawan (bawahan HR)
        $employee = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $hr->id_user,
        ]);

        // Buat atasan bawahan admin
        $atasan = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
            'atasan_id' => $admin->id_user,
        ]);

        // Buat bawahan atasan
        $bawahan1 = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $atasan->id_user,
            'nama' => 'Bawahan Satu',
        ]);
        $bawahan2 = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $atasan->id_user,
            'nama' => 'Bawahan Dua',
        ]);

        // Buat assessment untuk bawahan (sudah_dicek)
        KpiAssessment::create([
            'user_id' => $bawahan1->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $atasan->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 85.5,
        ]);

        // Buat assessment waiting review
        KpiAssessment::create([
            'user_id' => $bawahan2->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $atasan->id_user,
            'status' => 'menunggu_review',
            'skor_akhir' => null,
        ]);

        // Assessment periode sebelumnya
        KpiAssessment::create([
            'user_id' => $bawahan1->id_user,
            'kpi_period_id' => $this->prevPeriod->id,
            'atasan_id' => $atasan->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 80.0,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('kpi.dashboard', ['period_id' => $this->period->id]));

        $response->assertStatus(200);

        // Admin melihat semua karyawan aktif tanpa ADMIN = HR + ATASAN + KARYAWAN + bawahan (5 user)
        // Tapi yang dihitung berdasarkan scope query: semua aktif tanpa ADMIN = 5
        $response->assertSeeText('Total Karyawan');

        // Sudah dinilai = 1 (bawahan1)
        $response->assertSeeText('Sudah Dinilai');

        // Rata-rata KPI
        $response->assertSee('85.5');
    }

    /** @test */
    public function hr_can_view_kpi_dashboard_and_see_all_data()
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $hr = User::factory()->create([
            'role' => 'HR',
            'status' => true,
            'atasan_id' => $admin->id_user,
        ]);

        $employee = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $hr->id_user,
        ]);

        $atasan = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
            'atasan_id' => $admin->id_user,
        ]);

        // Assessment selesai
        KpiAssessment::create([
            'user_id' => $employee->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $hr->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 90.0,
        ]);

        $this->actingAs($hr);

        $response = $this->get(route('kpi.dashboard', ['period_id' => $this->period->id]));

        $response->assertStatus(200);
        $response->assertSeeText('Dashboard KPI');

        // HR melihat semua (tanpa ADMIN): ATASAN + KARYAWAN = 2
        $response->assertSeeText('Total Karyawan');
    }

    /** @test */
    public function regular_employee_can_only_see_own_data()
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $atasan = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
            'atasan_id' => $admin->id_user,
        ]);

        $employee = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $atasan->id_user,
        ]);

        // Karyawan lain
        $otherEmployee = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $atasan->id_user,
            'nama' => 'Other Employee',
        ]);

        // Assessment untuk employee
        KpiAssessment::create([
            'user_id' => $employee->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $atasan->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 75.0,
        ]);

        // Assessment untuk other employee
        KpiAssessment::create([
            'user_id' => $otherEmployee->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $atasan->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 88.0,
        ]);

        $this->actingAs($employee);

        $response = $this->get(route('kpi.dashboard', ['period_id' => $this->period->id]));

        $response->assertStatus(200);

        // Karyawan reguler hanya melihat dirinya sendiri = 1
        $response->assertSeeText('Total Karyawan');

        // Tidak melihat data employee lain
        $response->assertDontSee('Other Employee');

        // Melihat skor dirinya sendiri
        $response->assertSee('75.0');
    }

    /** @test */
    public function atasan_can_only_see_subordinate_data()
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $atasan = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
            'atasan_id' => $admin->id_user,
            'nama' => 'Atasan Utama', // Nama spesifik untuk menghindari conflict
        ]);

        // Bawahannya
        $bawahan1 = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $atasan->id_user,
            'nama' => 'Bawahan Satu',
        ]);
        $bawahan2 = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $atasan->id_user,
            'nama' => 'Bawahan Dua',
        ]);

        // Karyawan di luar atasan (langsung di bawah admin)
        $otherEmployee = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $admin->id_user,
            'nama' => 'Bukan Bawahan Atasan',
        ]);

        // Assessment bawahan 1 - selesai
        KpiAssessment::create([
            'user_id' => $bawahan1->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $atasan->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 85.0,
        ]);

        // Assessment bawahan 2 - menunggu review
        KpiAssessment::create([
            'user_id' => $bawahan2->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $atasan->id_user,
            'status' => 'menunggu_review',
            'skor_akhir' => null,
        ]);

        // Assessment bukan bawahan (skor unik 99.9 untuk deteksi)
        KpiAssessment::create([
            'user_id' => $otherEmployee->id_user,
            'kpi_period_id' => $this->period->id,
            'atasan_id' => $admin->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 99.9,
        ]);

        $this->actingAs($atasan);

        $response = $this->get(route('kpi.dashboard', ['period_id' => $this->period->id]));

        $response->assertStatus(200);

        // Melihat bawahannya (skor mereka)
        $response->assertSee('85.0');

        // Tidak melihat skor bukan bawahan
        $response->assertDontSee('99.9');
    }

    /** @test */
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

    /** @test */
    public function unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->get(route('kpi.dashboard'));

        $response->assertRedirect(route('login'));
    }
}
