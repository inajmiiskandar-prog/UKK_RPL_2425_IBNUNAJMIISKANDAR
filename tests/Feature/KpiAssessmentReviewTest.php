<?php

namespace Tests\Feature;

use App\Models\KpiAssessment;
use App\Models\KpiHardSkill;
use App\Models\KpiSoftSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiAssessmentReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_self_submission_waits_for_supervisor_and_review_calculates_final_score(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);
        $employee = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $supervisor->id_user,
            'status' => true,
        ]);
        $skill = KpiSoftSkill::create([
            'kode' => 'SS-REVIEW',
            'nama_indikator' => 'Communication',
        ]);

        $this->actingAs($employee)
            ->get(route('kpi.assessment.self'))
            ->assertOk()
            ->assertSee('Communication');

        $assessment = KpiAssessment::where('user_id', $employee->id_user)->firstOrFail();

        $this->post(route('kpi.assessment.self.store', $assessment), [
            'scores' => ['soft_skill' => [$skill->id => 80]],
        ])->assertRedirect(route('kpi.assessment.index'));

        $this->assertDatabaseHas('kpi_assessments', [
            'id' => $assessment->id,
            'status' => 'menunggu_review',
        ]);

        $this->actingAs($employee)
            ->get(route('kpi.assessment.show', $assessment->id))
            ->assertOk()
            ->assertSee('Menunggu review atasan')
            ->assertDontSee('onclick="printAssessment()"', false);

        $this->actingAs($supervisor)
            ->get(route('kpi.assessment.index'))
            ->assertOk()
            ->assertSee('Review');

        $this->get(route('kpi.assessment.create', ['assessment_id' => $assessment->id]))
            ->assertOk()
            ->assertSee('Konfirmasi Review Atasan');

        $this->post(route('kpi.assessment.wizard.step1'), [
            'assessment_id' => $assessment->id,
        ])->assertRedirect(route('kpi.assessment.wizard.step2'));

        $this->get(route('kpi.assessment.wizard.step2'))
            ->assertOk()
            ->assertSee('Self-assessment karyawan:')
            ->assertSee('>80<', false);

        $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$skill->id => 60],
        ])->assertRedirect(route('kpi.assessment.wizard.step3'));

        $this->get(route('kpi.assessment.wizard.step3'))
            ->assertRedirect(route('kpi.assessment.wizard.step4'));

        $this->post(route('kpi.assessment.wizard.submit'))
            ->assertRedirect(route('kpi.assessment.index'));

        $this->assertDatabaseHas('kpi_assessments', [
            'id' => $assessment->id,
            'status' => 'sudah_dicek',
            'skor_akhir' => 70,
        ]);

        $this->get(route('kpi.assessment.show', $assessment->id))
            ->assertOk()
            ->assertSee('Sudah selesai dinilai')
            ->assertSee('onclick="printAssessment()"', false)
            ->assertSee('function printAssessment()', false);

        $this->actingAs($employee)
            ->get(route('kpi.assessment.index'))
            ->assertOk()
            ->assertSee('Sudah selesai dinilai - Skor: 70.0');

        $this->get(route('kpi.assessment.history'))
            ->assertOk()
            ->assertSee('Sudah selesai dinilai');
    }

    public function test_review_calculates_weighted_hard_skill_from_combined_indicator_scores(): void
    {
        $supervisor = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $employee = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $supervisor->id_user,
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'status' => true,
        ]);
        $softSkill = KpiSoftSkill::create(['kode' => 'SS-WEIGHT', 'nama_indikator' => 'Commitment']);
        $hardSkillA = KpiHardSkill::create([
            'kode' => 'HS-WEIGHT-A', 'nama_indikator' => 'Kualitas kode',
            'kpi' => 'Kualitas kode', 'divisi' => 'IT', 'jabatan' => 'programer', 'weight' => 34,
        ]);
        $hardSkillB = KpiHardSkill::create([
            'kode' => 'HS-WEIGHT-B', 'nama_indikator' => 'Dokumentasi',
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

        $this->actingAs($supervisor)
            ->post(route('kpi.assessment.wizard.step1'), ['assessment_id' => $assessment->id])
            ->assertRedirect(route('kpi.assessment.wizard.step2'));
        $this->post(route('kpi.assessment.wizard.step2.store'), ['scores' => [$softSkill->id => 60]])
            ->assertRedirect(route('kpi.assessment.wizard.step3'));
        $this->post(route('kpi.assessment.wizard.step3.store'), [
            'scores' => [$hardSkillA->id => 60, $hardSkillB->id => 100],
        ])->assertRedirect(route('kpi.assessment.wizard.step4'));
        $this->post(route('kpi.assessment.wizard.submit'))
            ->assertRedirect(route('kpi.assessment.index'));

        // Soft combined average: 70. Hard weighted average: (70x34 + 80x66) / 100 = 76.6.
        $this->assertDatabaseHas('kpi_assessments', [
            'id' => $assessment->id,
            'status' => 'sudah_dicek',
            'skor_akhir' => 73.3,
        ]);
    }
}
