<?php

namespace Tests\Feature;

use App\Models\KpiAssessment;
use App\Models\KpiAssessmentScore;
use App\Models\KpiSoftSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiSelfAssessmentLockTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $attributes): User
    {
        /** @var User $user */
        $user = User::factory()->create($attributes);

        return $user;
    }

    private function getOrCreateAssessment(User $user): KpiAssessment
    {
        $this->actingAs($user)->get(route('kpi.assessment.self'))->assertOk();

        return KpiAssessment::where('user_id', $user->id_user)->firstOrFail();
    }

    public function test_self_assessment_save_is_rejected_for_checked_and_completed_assessments(): void
    {
        $skill = KpiSoftSkill::create([
            'kode' => 'SS-LOCK',
            'nama_indikator' => 'Komunikasi',
        ]);

        foreach (['sudah_dicek', 'selesai'] as $status) {
            $employee = $this->createUser(['role' => 'TEKNISI', 'status' => true]);
            $assessment = $this->getOrCreateAssessment($employee);
            KpiAssessmentScore::create([
                'kpi_assessment_id' => $assessment->id,
                'skill_type' => 'soft_skill',
                'skill_id' => $skill->id,
                'penilai_type' => 'karyawan',
                'skor' => 55,
                'catatan' => 'Nilai awal',
            ]);
            $assessment->update(['status' => $status, 'skor_akhir' => 77.5]);

            $this->from(route('kpi.assessment.self'))
                ->actingAs($employee)
                ->post(route('kpi.assessment.self.store', $assessment), [
                    'scores' => ['soft_skill' => [$skill->id => 99]],
                ])
                ->assertRedirect(route('kpi.assessment.self'))
                ->assertSessionHas('error', 'Penilaian ini sudah dicek atasan dan tidak dapat diubah.');

            $this->assertDatabaseHas('kpi_assessments', [
                'id' => $assessment->id,
                'status' => $status,
                'skor_akhir' => 77.5,
            ]);
            $this->assertDatabaseHas('kpi_assessment_scores', [
                'kpi_assessment_id' => $assessment->id,
                'skill_id' => $skill->id,
                'penilai_type' => 'karyawan',
                'skor' => 55,
                'catatan' => 'Nilai awal',
            ]);
            $this->assertSame(
                1,
                KpiAssessmentScore::where('kpi_assessment_id', $assessment->id)->count()
            );

            $this->get(route('kpi.assessment.self'))
                ->assertOk()
                ->assertSee('Penilaian ini sudah dicek atasan dan tidak dapat diubah.')
                ->assertDontSee('Simpan Self Assessment');
        }
    }

    public function test_self_assessment_save_still_works_while_waiting_for_supervisor_review(): void
    {
        $employee = $this->createUser(['role' => 'TEKNISI', 'status' => true]);
        $assessment = $this->getOrCreateAssessment($employee);
        $skill = KpiSoftSkill::create([
            'kode' => 'SS-REVIEW-LOCK',
            'nama_indikator' => 'Komunikasi',
        ]);
        KpiAssessmentScore::create([
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'soft_skill',
            'skill_id' => $skill->id,
            'penilai_type' => 'karyawan',
            'skor' => 55,
        ]);
        $assessment->update(['status' => 'menunggu_review']);

        $this->actingAs($employee)
            ->post(route('kpi.assessment.self.store', $assessment), [
                'scores' => ['soft_skill' => [$skill->id => 80]],
            ])
            ->assertRedirect(route('kpi.assessment.index'))
            ->assertSessionHas('success', 'Self Assessment berhasil disimpan!');

        $this->assertDatabaseHas('kpi_assessments', [
            'id' => $assessment->id,
            'status' => 'menunggu_review',
            'skor_akhir' => null,
        ]);
        $this->assertDatabaseHas('kpi_assessment_scores', [
            'kpi_assessment_id' => $assessment->id,
            'skill_id' => $skill->id,
            'penilai_type' => 'karyawan',
            'skor' => 80,
        ]);
    }
}