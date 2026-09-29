<?php

namespace Tests\Feature;

use App\Models\KpiAssessment;
use App\Models\KpiAssessmentScore;
use App\Models\KpiHardSkill;
use App\Models\KpiPeriod;
use App\Models\KpiSoftSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiWizardPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_wizard_step1_submits_and_carries_selection_to_step2(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $employee = User::factory()->create([
            'role' => 'KARYAWAN',
            'divisi' => 'IT',
            'jabatan' => 'Developer',
            'status' => true,
        ]);

        KpiSoftSkill::create([
            'kode' => 'SS-STEP1',
            'nama_indikator' => 'Komunikasi',
            'deskripsi' => 'Komunikasi internal',
        ]);

        $this->actingAs($admin)
            ->get(route('kpi.assessment.create'))
            ->assertOk()
            ->assertSee('method="POST"', false)
            ->assertSee('name="period_value"', false)
            ->assertSee('name="user_id"', false)
            ->assertSee('type="submit"', false);

        $step1Response = $this->post(route('kpi.assessment.wizard.step1'), [
            'period_value' => '2026-09',
            'user_id' => $employee->id_user,
        ]);

        $step1Response
            ->assertStatus(302)
            ->assertRedirect(route('kpi.assessment.wizard.step2'));

        $step1Response->assertSessionHas('kpi_wizard.period_value', '2026-09');
        $step1Response->assertSessionHas('kpi_wizard.user_id', $employee->id_user);

        $this->get(route('kpi.assessment.wizard.step2'))
            ->assertOk()
            ->assertSee($employee->nama)
            ->assertSee('KPI September 2026');
    }

    public function test_wizard_can_complete_steps_one_through_four(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $employee = User::factory()->create([
            'role' => 'KARYAWAN',
            'divisi' => 'IT',
            'jabatan' => 'Developer',
            'status' => true,
        ]);

        $softSkill = KpiSoftSkill::create([
            'kode' => 'SS-FULL',
            'nama_indikator' => 'Komunikasi',
        ]);

        $this->actingAs($admin);

        $this->post(route('kpi.assessment.wizard.step1'), [
            'period_value' => '2026-09',
            'user_id' => $employee->id_user,
        ])->assertRedirect(route('kpi.assessment.wizard.step2'));

        $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$softSkill->id => 80],
            'notes' => [$softSkill->id => 'Baik'],
        ])->assertRedirect(route('kpi.assessment.wizard.step3'));

        $this->get(route('kpi.assessment.wizard.step3'))
            ->assertOk()
            ->assertSee('Step 3: Penilaian Hard Skill');

        $this->post(route('kpi.assessment.wizard.step3.store'), [
            'skip' => '1',
        ])->assertRedirect(route('kpi.assessment.wizard.step4'));

        $this->get(route('kpi.assessment.wizard.step4'))
            ->assertOk()
            ->assertSee($employee->nama)
            ->assertSee('KPI September 2026');

        $this->post(route('kpi.assessment.wizard.submit'))
            ->assertRedirect(route('kpi.assessment.index'));

        $this->assertDatabaseHas('kpi_assessments', [
            'user_id' => $employee->id_user,
            'status' => 'atasan_done',
        ]);
    }

    public function test_wizard_does_not_delete_existing_scores_when_saving(): void
    {
        $user = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
        ]);

        $period = KpiPeriod::create([
            'nama' => 'Q2 2026',
            'tanggal_mulai' => '2026-04-01',
            'tanggal_selesai' => '2026-06-30',
            'status' => 'aktif',
        ]);

        $softSkillA = KpiSoftSkill::create([
            'kode' => 'SS-10',
            'nama_indikator' => 'Komunikasi',
            'deskripsi' => 'Komunikasi internal',
        ]);

        $softSkillB = KpiSoftSkill::create([
            'kode' => 'SS-11',
            'nama_indikator' => 'Kerja Tim',
            'deskripsi' => 'Kerja sama tim',
        ]);

        $hardSkill = KpiHardSkill::create([
            'kode' => 'HS-10',
            'nama_indikator' => 'Penguasaan Teknis',
            'deskripsi' => 'Skill teknis',
        ]);

        $this->actingAs($user);

        $assessment = KpiAssessment::create([
            'user_id' => $user->id_user,
            'kpi_period_id' => $period->id,
            'atasan_id' => $user->id_user,
            'status' => 'pending',
        ]);

        KpiAssessmentScore::create([
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'soft_skill',
            'skill_id' => $softSkillB->id,
            'penilai_type' => 'atasan',
            'skor' => 55,
            'catatan' => 'Data lama',
        ]);

        session()->put('kpi_wizard', [
            'period_id' => $period->id,
            'period_nama' => $period->nama,
            'user_id' => $user->id_user,
            'user_nama' => $user->nama,
            'atasan_id' => $user->id_user,
            'soft_skills' => [$softSkillA->id => 80],
            'soft_notes' => [$softSkillA->id => 'Baik'],
            'hard_skills' => [$hardSkill->id => 90],
            'hard_notes' => [$hardSkill->id => 'Bagus'],
        ]);

        $this->post(route('kpi.assessment.wizard.submit'))->assertRedirect(route('kpi.assessment.index'));

        $this->assertDatabaseHas('kpi_assessment_scores', [
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'soft_skill',
            'skill_id' => $softSkillB->id,
            'penilai_type' => 'atasan',
            'skor' => 55,
        ]);

        $this->assertEquals(3, $assessment->fresh()->scores()->count());
    }

    public function test_wizard_keeps_values_across_steps_and_saves_all_scores(): void
    {
        $user = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
        ]);

        $period = KpiPeriod::create([
            'nama' => 'Q1 2026',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-03-31',
            'status' => 'aktif',
        ]);

        $softSkill = KpiSoftSkill::create([
            'kode' => 'SS-01',
            'nama_indikator' => 'Komunikasi',
            'deskripsi' => 'Komunikasi internal',
        ]);

        $hardSkill = KpiHardSkill::create([
            'kode' => 'HS-01',
            'nama_indikator' => 'Penguasaan Teknis',
            'deskripsi' => 'Skill teknis',
        ]);

        $this->actingAs($user);

        session()->put('kpi_wizard', [
            'period_id' => $period->id,
            'period_nama' => $period->nama,
            'user_id' => $user->id_user,
            'user_nama' => $user->nama,
            'atasan_id' => $user->id_user,
        ]);

        $step2Response = $this->get(route('kpi.assessment.wizard.step2'));
        $step2Response->assertOk();

        $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$softSkill->id => 80],
            'notes' => [$softSkill->id => 'Baik'],
        ])->assertRedirect(route('kpi.assessment.wizard.step3'));

        $step2AfterPost = $this->get(route('kpi.assessment.wizard.step2'));
        $step2AfterPost->assertOk();
        $step2AfterPost->assertSee('value="80"', false);

        $this->post(route('kpi.assessment.wizard.step3.store'), [
            'scores' => [$hardSkill->id => 90],
            'notes' => [$hardSkill->id => 'Bagus'],
        ])->assertRedirect(route('kpi.assessment.wizard.step4'));

        $step3AfterPost = $this->get(route('kpi.assessment.wizard.step3'));
        $step3AfterPost->assertOk();
        $step3AfterPost->assertSee('value="90"', false);

        $this->post(route('kpi.assessment.wizard.submit'))
            ->assertRedirect(route('kpi.assessment.index'));

        $assessment = KpiAssessment::where('user_id', $user->id_user)
            ->where('kpi_period_id', $period->id)
            ->firstOrFail();

        $this->assertSame('atasan_done', $assessment->status);
        $this->assertEquals(2, $assessment->scores()->count());
        $this->assertSame(80, $assessment->scores()->where('skill_type', 'soft_skill')->first()->skor);
        $this->assertSame(90, $assessment->scores()->where('skill_type', 'hard_skill')->first()->skor);
    }
}
