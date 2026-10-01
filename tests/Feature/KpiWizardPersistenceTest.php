<?php

namespace Tests\Feature;

use App\Models\KpiAssessment;
use App\Models\KpiHardSkill;
use App\Models\KpiSoftSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Wizard KPI dual-mode:
 * - Review Mode (ada assessment_id): lanjut Step 2-4, atasan menilai ulang.
 * - Legacy Mode (tanpa assessment_id): dialihkan ke halaman self-assessment.
 */
class KpiWizardPersistenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Siapkan assessment berstatus menunggu_review (self-assessment sudah diisi karyawan).
     */
    private function prepareReview(): array
    {
        $leader = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leader->id_user,
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'status' => true,
        ]);

        $softSkill = KpiSoftSkill::create(['kode' => 'SS-WIZ', 'nama_indikator' => 'Commitment']);
        $hardSkillA = KpiHardSkill::create([
            'kode' => 'HS-WIZ-A', 'nama_indikator' => 'Kualitas kode',
            'kpi' => 'Kualitas kode', 'divisi' => 'IT', 'jabatan' => 'programer', 'weight' => 34,
        ]);
        $hardSkillB = KpiHardSkill::create([
            'kode' => 'HS-WIZ-B', 'nama_indikator' => 'Dokumentasi',
            'kpi' => 'Dokumentasi', 'divisi' => 'IT', 'jabatan' => 'programer', 'weight' => 66,
        ]);

        $this->actingAs($teknisi)->get(route('kpi.assessment.self'));
        $assessment = KpiAssessment::where('user_id', $teknisi->id_user)->firstOrFail();

        $this->post(route('kpi.assessment.self.store', $assessment), [
            'scores' => [
                'soft_skill' => [$softSkill->id => 80],
                'hard_skill' => [$hardSkillA->id => 80, $hardSkillB->id => 60],
            ],
        ])->assertRedirect(route('kpi.assessment.index'));

        return compact('leader', 'teknisi', 'assessment', 'softSkill', 'hardSkillA', 'hardSkillB');
    }

    public function test_wizard_step1_without_assessment_id_redirects_to_self_assessment(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $teknisi = User::factory()->create(['role' => 'TEKNISI', 'status' => true]);

        $this->actingAs($admin)
            ->post(route('kpi.assessment.wizard.step1'), [
                'period_value' => '2026-09',
                'user_id' => $teknisi->id_user,
            ])
            ->assertRedirect(route('kpi.assessment.self'));
    }

    public function test_wizard_step1_review_mode_carries_assessment_to_step2(): void
    {
        $d = $this->prepareReview();

        $response = $this->actingAs($d['leader'])
            ->post(route('kpi.assessment.wizard.step1'), ['assessment_id' => $d['assessment']->id]);

        $response->assertRedirect(route('kpi.assessment.wizard.step2'));
        $response->assertSessionHas('kpi_wizard.assessment_id', $d['assessment']->id);
        $response->assertSessionHas('kpi_wizard.user_id', $d['teknisi']->id_user);

        $this->get(route('kpi.assessment.wizard.step2'))->assertOk();
    }

    public function test_wizard_keeps_values_in_session_across_steps(): void
    {
        $d = $this->prepareReview();

        $this->actingAs($d['leader'])
            ->post(route('kpi.assessment.wizard.step1'), ['assessment_id' => $d['assessment']->id]);

        $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$d['softSkill']->id => 60],
            'notes' => [$d['softSkill']->id => 'Cukup'],
        ])->assertRedirect(route('kpi.assessment.wizard.step3'))
          ->assertSessionHas('kpi_wizard.atasan_soft_skills.' . $d['softSkill']->id, 60);

        $this->post(route('kpi.assessment.wizard.step3.store'), [
            'scores' => [$d['hardSkillA']->id => 60, $d['hardSkillB']->id => 100],
        ])->assertRedirect(route('kpi.assessment.wizard.step4'))
          ->assertSessionHas('kpi_wizard.atasan_hard_skills.' . $d['hardSkillB']->id, 100);
    }

    public function test_wizard_can_complete_review_and_calculates_final_score(): void
    {
        $d = $this->prepareReview();

        $this->actingAs($d['leader'])
            ->post(route('kpi.assessment.wizard.step1'), ['assessment_id' => $d['assessment']->id])
            ->assertRedirect(route('kpi.assessment.wizard.step2'));

        $this->post(route('kpi.assessment.wizard.step2.store'), ['scores' => [$d['softSkill']->id => 60]])
            ->assertRedirect(route('kpi.assessment.wizard.step3'));

        $this->post(route('kpi.assessment.wizard.step3.store'), [
            'scores' => [$d['hardSkillA']->id => 60, $d['hardSkillB']->id => 100],
        ])->assertRedirect(route('kpi.assessment.wizard.step4'));

        $this->post(route('kpi.assessment.wizard.submit'))
            ->assertRedirect(route('kpi.assessment.index'));

        // Soft: (80+60)/2 = 70. Hard berbobot: (70x34 + 80x66)/100 = 76.6. Akhir: 73.3
        $this->assertDatabaseHas('kpi_assessments', [
            'id' => $d['assessment']->id,
            'status' => 'sudah_dicek',
            'skor_akhir' => 73.3,
        ]);
    }

    public function test_wizard_submit_does_not_delete_self_assessment_scores(): void
    {
        $d = $this->prepareReview();

        $this->actingAs($d['leader'])
            ->post(route('kpi.assessment.wizard.step1'), ['assessment_id' => $d['assessment']->id]);
        $this->post(route('kpi.assessment.wizard.step2.store'), ['scores' => [$d['softSkill']->id => 60]]);
        $this->post(route('kpi.assessment.wizard.step3.store'), ['skip' => '1']);
        $this->post(route('kpi.assessment.wizard.submit'))->assertRedirect(route('kpi.assessment.index'));

        $this->assertDatabaseHas('kpi_assessment_scores', [
            'kpi_assessment_id' => $d['assessment']->id,
            'skill_type' => 'soft_skill',
            'skill_id' => $d['softSkill']->id,
            'penilai_type' => 'karyawan',
            'skor' => 80,
        ]);
        $this->assertDatabaseHas('kpi_assessment_scores', [
            'kpi_assessment_id' => $d['assessment']->id,
            'skill_type' => 'soft_skill',
            'skill_id' => $d['softSkill']->id,
            'penilai_type' => 'atasan',
            'skor' => 60,
        ]);
    }

    public function test_wizard_step3_and_step4_pages_render_in_review_mode(): void
    {
        $d = $this->prepareReview();

        $this->actingAs($d['leader'])
            ->post(route('kpi.assessment.wizard.step1'), ['assessment_id' => $d['assessment']->id]);
        $this->post(route('kpi.assessment.wizard.step2.store'), ['scores' => [$d['softSkill']->id => 60]]);

        $this->get(route('kpi.assessment.wizard.step3'))->assertOk();
        $this->post(route('kpi.assessment.wizard.step3.store'), [
            'scores' => [$d['hardSkillA']->id => 60, $d['hardSkillB']->id => 100],
        ]);
        $this->get(route('kpi.assessment.wizard.step4'))->assertOk();
    }

    // =====================================================
    // NEW TESTS - Role-based access control
    // =====================================================

    /**
     * Test: User yang BUKAN atasan langsung dan BUKAN ADMIN
     * harus ditolak saat mencoba mereview assessment orang lain
     */
    public function test_non_atasan_cannot_review_other_user_assessment(): void
    {
        // Leader A dengan bawahan TEKNISI
        $leaderA = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $teknisiA = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leaderA->id_user,
            'status' => true,
        ]);

        // Leader B dengan bawahan TEKNISI lain
        $leaderB = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);
        $teknisiB = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leaderB->id_user,
            'status' => true,
        ]);

        // TEKNISI A submit self-assessment
        $softSkill = KpiSoftSkill::create(['kode' => 'SS-TEST', 'nama_indikator' => 'Test Skill']);
        $this->actingAs($teknisiA)->get(route('kpi.assessment.self'));
        $assessment = KpiAssessment::where('user_id', $teknisiA->id_user)->firstOrFail();
        $this->post(route('kpi.assessment.self.store', $assessment), [
            'scores' => ['soft_skill' => [$softSkill->id => 80]],
        ]);

        // Leader B mencoba mereview assessment TEKNISI A (bukan bawahannya) - HARUS DITOLAK
        $this->actingAs($leaderB);
        $response = $this->post(route('kpi.assessment.wizard.step1'), ['assessment_id' => $assessment->id]);
        $response->assertStatus(403);

        // TEKNISI B (juga bukan atasan langsung) juga HARUS DITOLAK
        $this->actingAs($teknisiB);
        $response = $this->post(route('kpi.assessment.wizard.step1'), ['assessment_id' => $assessment->id]);
        $response->assertStatus(403);

        // Leader A (atasan langsung) boleh mereview
        $this->actingAs($leaderA);
        $response = $this->post(route('kpi.assessment.wizard.step1'), ['assessment_id' => $assessment->id]);
        $response->assertStatus(302); // Redirect, bukan 403
    }

    /**
     * Test: User tanpa bawahan hanya melihat assessment dirinya sendiri
     */
    public function test_user_without_subordinates_sees_only_own_assessment(): void
    {
        $leader = User::factory()->create(['role' => 'LEADER', 'status' => true]);

        // SALES tanpa bawahan
        $sales = User::factory()->create([
            'role' => 'SALES',
            'atasan_id' => $leader->id_user,
            'status' => true,
        ]);

        // TEKNISI bawahan leader
        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leader->id_user,
            'status' => true,
        ]);

        // Buat assessment untuk sales
        $softSkill = KpiSoftSkill::create(['kode' => 'SS-SALES', 'nama_indikator' => 'Sales Skill']);
        $this->actingAs($sales)->get(route('kpi.assessment.self'));
        $salesAssessment = KpiAssessment::where('user_id', $sales->id_user)->firstOrFail();
        $this->post(route('kpi.assessment.self.store', $salesAssessment), [
            'scores' => ['soft_skill' => [$softSkill->id => 80]],
        ]);

        // Buat assessment untuk teknisi
        $this->actingAs($teknisi)->get(route('kpi.assessment.self'));
        $teknisiAssessment = KpiAssessment::where('user_id', $teknisi->id_user)->firstOrFail();
        $this->post(route('kpi.assessment.self.store', $teknisiAssessment), [
            'scores' => ['soft_skill' => [$softSkill->id => 70]],
        ]);

        // SALES melihat histori - harusnya hanya melihat assessment dirinya sendiri
        $this->actingAs($sales);
        $response = $this->get(route('kpi.assessment.history'));
        $response->assertStatus(200);

        // Verifikasi via viewData: hanya 1 assessment dan miliknya sales
        $response->assertViewHas('assessments');
        $viewAssessments = $response->viewData('assessments');
        $this->assertCount(1, $viewAssessments);
        $this->assertEquals($sales->id_user, $viewAssessments->first()->user_id);

        // Nama teknisi tidak boleh muncul di histori sales (notifikasi atau konten)
        $response->assertDontSee($teknisi->nama);
    }

    /**
     * Test: ADMIN bisa mereview assessment siapa saja
     */
    public function test_admin_can_review_any_assessment(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $leader = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leader->id_user,
            'status' => true,
        ]);

        // TEKNISI submit self-assessment
        $softSkill = KpiSoftSkill::create(['kode' => 'SS-ADMIN', 'nama_indikator' => 'Admin Test']);
        $this->actingAs($teknisi)->get(route('kpi.assessment.self'));
        $assessment = KpiAssessment::where('user_id', $teknisi->id_user)->firstOrFail();
        $this->post(route('kpi.assessment.self.store', $assessment), [
            'scores' => ['soft_skill' => [$softSkill->id => 80]],
        ]);

        // ADMIN boleh mereview assessment siapa saja
        $this->actingAs($admin);
        $response = $this->post(route('kpi.assessment.wizard.step1'), ['assessment_id' => $assessment->id]);
        $response->assertStatus(302); // Redirect, bukan 403
    }
}
