<?php

namespace Tests\Feature;

use App\Models\KpiAssessment;
use App\Models\KpiHardSkill;
use App\Models\KpiSoftSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk slider 1-100 di wizard review step 2 dan step 3.
 * Menggantikan dropdown 5-level (20, 40, 60, 80, 100).
 */
class KpiWizardSliderTest extends TestCase
{
    use RefreshDatabase;

    private function setupAssessment(): array
    {
        $leader = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leader->id_user,
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'status' => true,
        ]);

        $softSkill = KpiSoftSkill::create(['kode' => 'SS-SLIDER', 'nama_indikator' => 'Komunikasi']);
        $hardSkill = KpiHardSkill::create([
            'kode' => 'HS-SLIDER', 'nama_indikator' => 'Coding',
            'kpi' => 'Koding', 'divisi' => 'IT', 'jabatan' => 'programer', 'weight' => 100,
        ]);

        // Teknisi isi self-assessment
        $this->actingAs($teknisi)->get(route('kpi.assessment.self'));
        $assessment = KpiAssessment::where('user_id', $teknisi->id_user)->firstOrFail();
        $this->post(route('kpi.assessment.self.store', $assessment), [
            'scores' => [
                'soft_skill' => [$softSkill->id => 50],
                'hard_skill' => [$hardSkill->id => 50],
            ],
        ]);

        // Leader mulai review
        $this->actingAs($leader)->post(route('kpi.assessment.wizard.step1'), [
            'assessment_id' => $assessment->id,
        ]);

        return compact('leader', 'teknisi', 'assessment', 'softSkill', 'hardSkill');
    }

    public function test_wizard_step2_accepts_score_1(): void
    {
        $d = $this->setupAssessment();

        $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$d['softSkill']->id => 1],
        ])->assertRedirect(route('kpi.assessment.wizard.step3'));
    }

    public function test_wizard_step2_accepts_score_100(): void
    {
        $d = $this->setupAssessment();

        $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$d['softSkill']->id => 100],
        ])->assertRedirect(route('kpi.assessment.wizard.step3'));
    }

    public function test_wizard_step2_rejects_score_0(): void
    {
        $d = $this->setupAssessment();

        $response = $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$d['softSkill']->id => 0],
        ]);

        $response->assertSessionHasErrors(['scores.' . $d['softSkill']->id]);
    }

    public function test_wizard_step2_rejects_score_101(): void
    {
        $d = $this->setupAssessment();

        $response = $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$d['softSkill']->id => 101],
        ]);

        $response->assertSessionHasErrors(['scores.' . $d['softSkill']->id]);
    }

    public function test_wizard_step3_accepts_score_1(): void
    {
        $d = $this->setupAssessment();

        // Step 2 dulu
        $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$d['softSkill']->id => 50],
        ]);

        // Step 3 dengan nilai 1
        $this->post(route('kpi.assessment.wizard.step3.store'), [
            'scores' => [$d['hardSkill']->id => 1],
        ])->assertRedirect(route('kpi.assessment.wizard.step4'));
    }

    public function test_wizard_step3_accepts_score_100(): void
    {
        $d = $this->setupAssessment();

        $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$d['softSkill']->id => 50],
        ]);

        $this->post(route('kpi.assessment.wizard.step3.store'), [
            'scores' => [$d['hardSkill']->id => 100],
        ])->assertRedirect(route('kpi.assessment.wizard.step4'));
    }

    public function test_wizard_step3_rejects_score_0(): void
    {
        $d = $this->setupAssessment();

        $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$d['softSkill']->id => 50],
        ]);

        $response = $this->post(route('kpi.assessment.wizard.step3.store'), [
            'scores' => [$d['hardSkill']->id => 0],
        ]);

        $response->assertSessionHasErrors(['scores.' . $d['hardSkill']->id]);
    }

    public function test_wizard_step3_rejects_score_101(): void
    {
        $d = $this->setupAssessment();

        $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$d['softSkill']->id => 50],
        ]);

        $response = $this->post(route('kpi.assessment.wizard.step3.store'), [
            'scores' => [$d['hardSkill']->id => 101],
        ]);

        $response->assertSessionHasErrors(['scores.' . $d['hardSkill']->id]);
    }

    public function test_wizard_step2_page_contains_slider(): void
    {
        $d = $this->setupAssessment();

        $response = $this->get(route('kpi.assessment.wizard.step2'));
        $response->assertOk();

        // Memuat slider range input
        $content = $response->getContent();
        $this->assertStringContainsString('type="range"', $content);
        // Tidak memuat dropdown 5-level
        $this->assertStringNotContainsString('Sangat Baik (100)', $content);
        $this->assertStringNotContainsString('Sangat Kurang (20)', $content);
    }

    public function test_wizard_step3_page_contains_slider(): void
    {
        $d = $this->setupAssessment();

        $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$d['softSkill']->id => 50],
        ]);

        $response = $this->get(route('kpi.assessment.wizard.step3'));
        $response->assertOk();

        // Memuat slider range input
        $content = $response->getContent();
        $this->assertStringContainsString('type="range"', $content);
        // Tidak memuat dropdown 5-level
        $this->assertStringNotContainsString('Sangat Baik (100)', $content);
        $this->assertStringNotContainsString('Sangat Kurang (20)', $content);
    }

    public function test_final_score_is_correct_average_of_self_and_atasan(): void
    {
        $d = $this->setupAssessment();

        // Self: 50, Atasan: 80 -> Soft avg = (50+80)/2 = 65
        // Self: 50, Atasan: 80 -> Hard avg = (50+80)/2 = 65
        // Final = (65+65)/2 = 65

        $this->post(route('kpi.assessment.wizard.step2.store'), [
            'scores' => [$d['softSkill']->id => 80],
        ]);

        $this->post(route('kpi.assessment.wizard.step3.store'), [
            'scores' => [$d['hardSkill']->id => 80],
        ]);

        $this->post(route('kpi.assessment.wizard.submit'))
            ->assertRedirect(route('kpi.assessment.index'));

        $this->assertDatabaseHas('kpi_assessments', [
            'id' => $d['assessment']->id,
            'status' => 'sudah_dicek',
            'skor_akhir' => 65.0,
        ]);
    }

    public function test_slider_default_value_is_50(): void
    {
        $d = $this->setupAssessment();

        $response = $this->get(route('kpi.assessment.wizard.step2'));
        $response->assertOk();

        // Nilai default slider adalah 50
        $content = $response->getContent();
        $this->assertStringContainsString('value="50"', $content);
    }
}
