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
        $supervisor = User::factory()->create(['role' => 'ATASAN', 'status' => true]);
        $employee = User::factory()->create([
            'role' => 'KARYAWAN',
            'atasan_id' => $supervisor->id_user,
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

        $this->actingAs($employee)->get(route('kpi.assessment.self'));
        $assessment = KpiAssessment::where('user_id', $employee->id_user)->firstOrFail();

        $this->post(route('kpi.assessment.self.store', $assessment), [
            'scores' => [
                'soft_skill' => [$softSkill->id => 80],
                'hard_skill' => [$hardSkillA->id => 80, $hardSkillB->id => 60],
            ],
        ])->assertRedirect(route('kpi.assessment.index'));

        return compact('supervisor', 'employee', 'assessment', 'softSkill', 'hardSkillA', 'hardSkillB');
    }

    public function test_wizard_step1_without_assessment_id_redirects_to_self_assessment(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $employee = User::factory()->create(['role' => 'KARYAWAN', 'status' => true]);

        $this->actingAs($admin)
            ->post(route('kpi.assessment.wizard.step1'), [
                'period_value' => '2026-09',
                'user_id' => $employee->id_user,
            ])
            ->assertRedirect(route('kpi.assessment.self'));
    }

    public function test_wizard_step1_review_mode_carries_assessment_to_step2(): void
    {
        $d = $this->prepareReview();

        $response = $this->actingAs($d['supervisor'])
            ->post(route('kpi.assessment.wizard.step1'), ['assessment_id' => $d['assessment']->id]);

        $response->assertRedirect(route('kpi.assessment.wizard.step2'));
        $response->assertSessionHas('kpi_wizard.assessment_id', $d['assessment']->id);
        $response->assertSessionHas('kpi_wizard.user_id', $d['employee']->id_user);

        $this->get(route('kpi.assessment.wizard.step2'))->assertOk();
    }

    public function test_wizard_keeps_values_in_session_across_steps(): void
    {
        $d = $this->prepareReview();

        $this->actingAs($d['supervisor'])
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

        $this->actingAs($d['supervisor'])
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

        $this->actingAs($d['supervisor'])
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

        $this->actingAs($d['supervisor'])
            ->post(route('kpi.assessment.wizard.step1'), ['assessment_id' => $d['assessment']->id]);
        $this->post(route('kpi.assessment.wizard.step2.store'), ['scores' => [$d['softSkill']->id => 60]]);

        $this->get(route('kpi.assessment.wizard.step3'))->assertOk();
        $this->post(route('kpi.assessment.wizard.step3.store'), [
            'scores' => [$d['hardSkillA']->id => 60, $d['hardSkillB']->id => 100],
        ]);
        $this->get(route('kpi.assessment.wizard.step4'))->assertOk();
    }
}