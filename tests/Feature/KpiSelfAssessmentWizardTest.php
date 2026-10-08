<?php

namespace Tests\Feature;

use App\Models\KpiAssessment;
use App\Models\KpiAssessmentScore;
use App\Models\KpiHardSkill;
use App\Models\KpiSoftSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiSelfAssessmentWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_self_assessment_renders_two_steps_and_score_bubbles(): void
    {
        $user = User::factory()->create([
            'divisi' => 'IT',
            'jabatan' => 'programer',
        ]);
        $softSkill = KpiSoftSkill::create([
            'kode' => 'SS-STEP',
            'nama_indikator' => 'Komunikasi',
        ]);
        $hardSkill = KpiHardSkill::create([
            'kode' => 'HS-STEP',
            'nama_indikator' => 'Kualitas kerja',
            'kpi' => 'Kualitas kerja',
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'weight' => 100,
        ]);

        $response = $this->actingAs($user)->get(route('kpi.assessment.self'));
        $assessment = $response->viewData('assessment');

        $response->assertOk()
            ->assertSeeText('Langkah 1 dari 2')
            ->assertSee('Soft Skill')
            ->assertSee('Hard Skill')
            ->assertSee('Selanjutnya')
            ->assertSee('data-step-panel="soft"', false)
            ->assertSee('data-step-panel="hard"', false)
            ->assertSee('data-score-bubble', false)
            ->assertSee('name="scores[soft_skill][' . $softSkill->id . ']"', false)
            ->assertSee('name="scores[hard_skill][' . $hardSkill->id . ']"', false);

        $formAction = route('kpi.assessment.self.store', $assessment->id);
        $this->assertSame(1, substr_count($response->getContent(), 'action="' . $formAction . '"'));
    }

    public function test_self_assessment_submission_saves_soft_and_hard_scores_together(): void
    {
        $user = User::factory()->create([
            'divisi' => 'IT',
            'jabatan' => 'programer',
        ]);
        $softSkill = KpiSoftSkill::create([
            'kode' => 'SS-SAVE',
            'nama_indikator' => 'Kerja tim',
        ]);
        $hardSkill = KpiHardSkill::create([
            'kode' => 'HS-SAVE',
            'nama_indikator' => 'Pemecahan masalah',
            'kpi' => 'Pemecahan masalah',
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'weight' => 100,
        ]);

        $this->actingAs($user)->get(route('kpi.assessment.self'));
        $assessment = KpiAssessment::where('user_id', $user->id_user)->firstOrFail();

        $this->post(route('kpi.assessment.self.store', $assessment), [
            'scores' => [
                'soft_skill' => [$softSkill->id => 82],
                'hard_skill' => [$hardSkill->id => 91],
            ],
            'notes' => [
                'soft_skill' => [$softSkill->id => 'Catatan soft'],
                'hard_skill' => [$hardSkill->id => 'Catatan hard'],
            ],
        ])->assertRedirect(route('kpi.assessment.index'));

        $this->assertDatabaseHas('kpi_assessment_scores', [
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'soft_skill',
            'skill_id' => $softSkill->id,
            'penilai_type' => 'karyawan',
            'skor' => 82,
            'catatan' => 'Catatan soft',
        ]);
        $this->assertDatabaseHas('kpi_assessment_scores', [
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'hard_skill',
            'skill_id' => $hardSkill->id,
            'penilai_type' => 'karyawan',
            'skor' => 91,
            'catatan' => 'Catatan hard',
        ]);
        $this->assertSame(2, KpiAssessmentScore::where('kpi_assessment_id', $assessment->id)->count());
    }

    public function test_hard_skill_validation_error_opens_the_hard_skill_step(): void
    {
        $user = User::factory()->create([
            'divisi' => 'IT',
            'jabatan' => 'programer',
        ]);
        $softSkill = KpiSoftSkill::create([
            'kode' => 'SS-ERROR',
            'nama_indikator' => 'Komunikasi',
        ]);
        $hardSkill = KpiHardSkill::create([
            'kode' => 'HS-ERROR',
            'nama_indikator' => 'Kualitas kerja',
            'kpi' => 'Kualitas kerja',
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'weight' => 100,
        ]);

        $this->actingAs($user)->get(route('kpi.assessment.self'));
        $assessment = KpiAssessment::where('user_id', $user->id_user)->firstOrFail();

        $this->from(route('kpi.assessment.self'))
            ->post(route('kpi.assessment.self.store', $assessment), [
                'scores' => [
                    'soft_skill' => [$softSkill->id => 80],
                    'hard_skill' => [$hardSkill->id => 0],
                ],
            ])
            ->assertRedirect(route('kpi.assessment.self'));

        $response = $this->get(route('kpi.assessment.self'));

        $response->assertOk()
            ->assertSee('id="assessment-current-step">2</span>', false)
            ->assertSee('id="assessment-back"', false);
        $this->assertMatchesRegularExpression(
            '/<div id="soft-skill-step"[^>]*\shidden(?:\s|>)/',
            $response->getContent()
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<div id="hard-skill-step"[^>]*\shidden(?:\s|>)/',
            $response->getContent()
        );
    }

    public function test_soft_only_assessment_skips_next_step_and_shows_save(): void
    {
        $user = User::factory()->create();
        KpiSoftSkill::create([
            'kode' => 'SS-ONLY',
            'nama_indikator' => 'Komunikasi',
        ]);

        $response = $this->actingAs($user)->get(route('kpi.assessment.self'));

        $response->assertOk()
            ->assertSeeText('Langkah 1 dari 1')
            ->assertSee('Simpan Self Assessment')
            ->assertDontSee('Selanjutnya');
    }
}