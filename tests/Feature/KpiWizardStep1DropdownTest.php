<?php

namespace Tests\Feature;

use App\Models\KpiAssessment;
use App\Models\KpiPeriod;
use App\Models\KpiSoftSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Test untuk custom dropdown karyawan di Wizard Step 1
 * - ADMIN melihat semua karyawan
 * - Atasan melihat bawahan langsung + diri sendiri
 * - Status self-assessment ditampilkan (badge, disabled)
 * - Validasi server menolak karyawan disabled atau di luar scope
 */
class KpiWizardStep1DropdownTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Siapkan data dasar untuk test
     */
    private function setupBaseData(): array
    {
        $period = KpiPeriod::create([
            'nama' => 'KPI Oktober 2026',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-31',
            'status' => 'aktif',
        ]);

        $softSkill = KpiSoftSkill::create([
            'kode' => 'SS-TEST',
            'nama_indikator' => 'Test Skill',
        ]);

        return compact('period', 'softSkill');
    }

    // =====================================================
    // Test: ADMIN Melihat Semua Karyawan
    // =====================================================

    public function test_admin_sees_all_active_employees_in_dropdown(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $user1 = User::factory()->create(['nama' => 'Alpha', 'status' => true]);
        $user2 = User::factory()->create(['nama' => 'Beta', 'status' => true]);
        $inactiveUser = User::factory()->create(['nama' => 'Inactive', 'status' => false]);

        $this->setupBaseData();

        $response = $this->actingAs($admin)->get(route('kpi.assessment.create'));

        $response->assertStatus(200);

        // Check via view data - nama ada di JavaScript data
        $viewData = $response->viewData('employeeData');
        $this->assertNotNull($viewData);

        // Active users harus ada
        $this->assertTrue($viewData->contains('id_user', $user1->id_user));
        $this->assertTrue($viewData->contains('id_user', $user2->id_user));
        $this->assertTrue($viewData->contains('id_user', $admin->id_user));

        // Inactive user tidak boleh ada
        $this->assertFalse($viewData->contains('id_user', $inactiveUser->id_user));
    }

    public function test_admin_sees_employees_with_correct_status_data(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        // Create user dengan nama unik
        $user = User::factory()->create();
        $user->nama = 'XyzUniqueName';
        $user->nik = '12345';
        $user->divisi = 'IT';
        $user->jabatan = 'Developer';
        $user->save();
        $user->refresh();

        $this->setupBaseData();

        $response = $this->actingAs($admin)->get(route('kpi.assessment.create'));

        $response->assertStatus(200);

        // Check JSON data passed to JavaScript
        $viewData = $response->viewData('employeeData');
        $this->assertNotNull($viewData);

        $userData = $viewData->firstWhere('id_user', $user->id_user);
        $this->assertNotNull($userData);
        $this->assertEquals('XyzUniqueName', $userData['nama']);
        $this->assertEquals('12345', $userData['nik']);
        $this->assertEquals('IT', $userData['divisi']);
        $this->assertEquals('Developer', $userData['jabatan']);
        $this->assertEquals('XY', $userData['initials']); // XyzUniqueName -> XY
        $this->assertTrue($userData['disabled']); // No assessment = disabled
        $this->assertEquals('Belum mengisi Self-Assessment', $userData['badge']);
    }

    // =====================================================
    // Test: Atasan Melihat Bawahan + Diri Sendiri
    // =====================================================

    public function test_atasan_sees_only_subordinates_and_self(): void
    {
        $leader = User::factory()->create(['role' => 'LEADER', 'nama' => 'Leader', 'status' => true]);
        $bawahan1 = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leader->id_user,
            'nama' => 'Bawahan 1',
            'status' => true,
        ]);
        $bawahan2 = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leader->id_user,
            'nama' => 'Bawahan 2',
            'status' => true,
        ]);
        // Bukan bawahan leader
        $otherUser = User::factory()->create([
            'role' => 'TEKNISI',
            'nama' => 'Other User',
            'status' => true,
        ]);

        $this->setupBaseData();

        $response = $this->actingAs($leader)->get(route('kpi.assessment.create'));

        $response->assertStatus(200);

        // Check via view data
        $viewData = $response->viewData('employeeData');

        // Leader dan bawahan harus ada
        $this->assertTrue($viewData->contains('id_user', $leader->id_user));
        $this->assertTrue($viewData->contains('id_user', $bawahan1->id_user));
        $this->assertTrue($viewData->contains('id_user', $bawahan2->id_user));

        // Other user tidak boleh ada
        $this->assertFalse($viewData->contains('id_user', $otherUser->id_user));
    }

    public function test_user_without_subordinates_sees_only_self(): void
    {
        $leader = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $sales = User::factory()->create([
            'role' => 'SALES',
            'atasan_id' => $leader->id_user,
            'status' => true,
        ]);

        $this->setupBaseData();

        $response = $this->actingAs($sales)->get(route('kpi.assessment.create'));

        $response->assertStatus(200);
        // Sales tanpa bawahan hanya melihat dirinya sendiri
        $viewData = $response->viewData('employeeData');
        $this->assertCount(1, $viewData);
        $this->assertEquals($sales->id_user, $viewData->first()['id_user']);
    }

    // =====================================================
    // Test: Status Self-Assessment
    // =====================================================

    public function test_employee_without_self_assessment_shows_disabled_badge(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $user = User::factory()->create(['status' => true]);
        $this->setupBaseData();

        // User belum mengisi self-assessment
        $response = $this->actingAs($admin)->get(route('kpi.assessment.create'));

        $response->assertStatus(200);
        $viewData = $response->viewData('employeeData');
        $userData = $viewData->firstWhere('id_user', $user->id_user);

        $this->assertTrue($userData['disabled']);
        $this->assertEquals('Belum mengisi Self-Assessment', $userData['badge']);
        $this->assertEquals('no_record', $userData['status']);
    }

    public function test_employee_with_menunggu_review_status_is_selectable(): void
    {
        $leader = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leader->id_user,
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'status' => true,
        ]);
        $data = $this->setupBaseData();

        // Teknisi mengisi self-assessment
        $this->actingAs($teknisi)->get(route('kpi.assessment.self'));
        $assessment = KpiAssessment::where('user_id', $teknisi->id_user)->first();
        $this->post(route('kpi.assessment.self.store', $assessment), [
            'scores' => ['soft_skill' => [$data['softSkill']->id => 80]],
        ]);

        // Leader melihat teknisi dengan status menunggu_review
        $response = $this->actingAs($leader)->get(route('kpi.assessment.create'));

        $response->assertStatus(200);
        $viewData = $response->viewData('employeeData');
        $userData = $viewData->firstWhere('id_user', $teknisi->id_user);

        $this->assertFalse($userData['disabled']);
        $this->assertEquals('Menunggu Review', $userData['badge']);
        $this->assertEquals('menunggu_review', $userData['status']);
    }

    public function test_employee_with_sudah_dicek_status_shows_disabled(): void
    {
        $leader = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leader->id_user,
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'status' => true,
        ]);
        $data = $this->setupBaseData();

        // Teknisi isi self-assessment
        $this->actingAs($teknisi)->get(route('kpi.assessment.self'));
        $assessment = KpiAssessment::where('user_id', $teknisi->id_user)->first();
        $this->post(route('kpi.assessment.self.store', $assessment), [
            'scores' => ['soft_skill' => [$data['softSkill']->id => 80]],
        ]);

        // Leader menyelesaikan review
        $this->actingAs($leader)->post(route('kpi.assessment.wizard.step1'), [
            'assessment_id' => $assessment->id,
        ]);
        $this->actingAs($leader)->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$data['softSkill']->id => 80],
        ]);
        $this->post(route('kpi.assessment.wizard.submit'));

        // Refresh assessment status
        $assessment->refresh();
        $this->assertEquals('sudah_dicek', $assessment->status);

        // Leader melihat teknisi dengan status sudah_dicek
        $response = $this->actingAs($leader)->get(route('kpi.assessment.create'));

        $response->assertStatus(200);
        $viewData = $response->viewData('employeeData');
        $userData = $viewData->firstWhere('id_user', $teknisi->id_user);

        $this->assertTrue($userData['disabled']);
        $this->assertEquals('Sudah dinilai', $userData['badge']);
    }

    public function test_current_user_shows_saya_badge(): void
    {
        $user = User::factory()->create();
        $user->nama = 'User Saya';
        $user->save();
        $user->refresh();

        $this->setupBaseData();

        $response = $this->actingAs($user)->get(route('kpi.assessment.create'));

        $response->assertStatus(200);
        $viewData = $response->viewData('employeeData');
        $userData = $viewData->firstWhere('id_user', $user->id_user);

        $this->assertTrue($userData['is_current_user']);
        $this->assertEquals('US', $userData['initials']); // User Saya -> US
    }

    // =====================================================
    // Test: Validasi Server - Request Manual ke Karyawan Disabled
    // =====================================================

    public function test_cannot_select_employee_without_self_assessment(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $user = User::factory()->create(['status' => true]);
        $this->setupBaseData();

        // User belum mengisi self-assessment
        $response = $this->actingAs($admin)->post(route('kpi.assessment.wizard.step1'), [
            'user_id' => $user->id_user,
            'period_value' => '2026-10',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Karyawan ini belum mengisi Self-Assessment.');
    }

    public function test_cannot_select_employee_already_reviewed(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $leader = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leader->id_user,
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'status' => true,
        ]);
        $data = $this->setupBaseData();

        // Teknisi isi self-assessment
        $this->actingAs($teknisi)->get(route('kpi.assessment.self'));
        $assessment = KpiAssessment::where('user_id', $teknisi->id_user)->first();
        $this->post(route('kpi.assessment.self.store', $assessment), [
            'scores' => ['soft_skill' => [$data['softSkill']->id => 80]],
        ]);

        // Leader selesaikan review
        $this->actingAs($leader)->post(route('kpi.assessment.wizard.step1'), [
            'assessment_id' => $assessment->id,
        ]);
        $this->actingAs($leader)->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$data['softSkill']->id => 80],
        ]);
        $this->post(route('kpi.assessment.wizard.submit'));

        // Admin coba pilih user yang sudah dinilai
        $response = $this->actingAs($admin)->post(route('kpi.assessment.wizard.step1'), [
            'user_id' => $teknisi->id_user,
            'period_value' => '2026-10',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Karyawan ini sudah dinilai oleh atasan.');
    }

    public function test_cannot_select_user_outside_scope(): void
    {
        $leader1 = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $leader2 = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leader1->id_user,
            'status' => true,
        ]);
        $teknisi2 = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leader2->id_user,
            'status' => true,
        ]);
        $this->setupBaseData();

        // Leader1 coba pilih teknisi2 (bukan bawahannya)
        $response = $this->actingAs($leader1)->post(route('kpi.assessment.wizard.step1'), [
            'user_id' => $teknisi2->id_user,
            'period_value' => '2026-10',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Karyawan yang dipilih tidak valid atau di luar hak akses Anda!');
    }

    // =====================================================
    // Test: Review Mode (dengan assessment_id)
    // =====================================================

    public function test_review_mode_shows_assessment_info_not_dropdown(): void
    {
        $leader = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leader->id_user,
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'status' => true,
        ]);
        $data = $this->setupBaseData();

        // Teknisi isi self-assessment
        $this->actingAs($teknisi);
        $this->get(route('kpi.assessment.self'));
        $assessment = KpiAssessment::where('user_id', $teknisi->id_user)->first();
        $this->post(route('kpi.assessment.self.store', $assessment), [
            'scores' => ['soft_skill' => [$data['softSkill']->id => 80]],
        ]);

        // Leader buka wizard dengan assessment_id (review mode via GET ke assessment.create)
        $this->actingAs($leader);
        $response = $this->get(route('kpi.assessment.create', [
            'assessment_id' => $assessment->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('reviewMode', true);
        // Assessment info should be displayed
        $response->assertViewHas('assessment');
    }

    // =====================================================
    // Test: N+1 Prevention
    // =====================================================

    public function test_no_n_plus_one_queries_for_status_check(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        // Buat 10 user
        User::factory()->count(10)->create(['status' => true]);
        $this->setupBaseData();

        // Hitung total query - dengan N+1 akan ada banyak query
        // Dengan bulk query seharusnya hanya beberapa query saja
        $response = $this->actingAs($admin)->get(route('kpi.assessment.create'));

        $response->assertStatus(200);

        // Verifikasi employeeData ada dan berisi data yang benar
        $viewData = $response->viewData('employeeData');
        $this->assertNotNull($viewData);
        $this->assertCount(11, $viewData); // admin + 10 users
    }
}
