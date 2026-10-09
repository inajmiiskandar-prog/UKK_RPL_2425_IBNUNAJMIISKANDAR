<?php

namespace Tests\Feature;

use App\Models\KpiAssessment;
use App\Models\KpiAssessmentScore;
use App\Models\KpiHardSkill;
use App\Models\KpiPeriod;
use App\Models\KpiSoftSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Tests for the KPI Assessment Detail page (show.blade.php).
 *
 * Verifies that skill names and types are displayed correctly, especially when
 * soft_skill and hard_skill have overlapping IDs (e.g. both have id=1).
 * The root cause being tested: polymorphic-like skill() method must resolve
 * to the correct model based on skill_type, not just skill_id.
 */
class KpiAssessmentShowDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: create an active KPI period.
     */
    private function createPeriod(): KpiPeriod
    {
        return KpiPeriod::create([
            'nama' => 'KPI Januari 2025',
            'tanggal_mulai' => now()->startOfMonth(),
            'tanggal_selesai' => now()->endOfMonth(),
            'status' => 'aktif',
        ]);
    }

    /**
     * Core test: soft and hard skill with the same ID must not be swapped
     * on the assessment detail page, in all three sections.
     */
    public function test_detail_page_shows_correct_skill_names_and_types_when_ids_overlap(): void
    {
        // Arrange
        $period = $this->createPeriod();

        $supervisor = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);
        $employee = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $supervisor->id_user,
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'status' => true,
        ]);

        // Create soft skill with explicit id=1 (normally auto-increment, but factory lets us set it)
        $softSkill = KpiSoftSkill::create([
            'kode' => 'SS001',
            'nama_indikator' => 'Commitment',
        ]);
        // Override id to 1 to simulate overlapping IDs
        \DB::table('kpi_soft_skills')->where('id', $softSkill->id)->update(['id' => 1]);
        $softSkill->refresh(); // reload so $softSkill->id === 1

        // Create hard skill with explicit id=1 (same as soft skill)
        $hardSkill = KpiHardSkill::create([
            'kode' => 'HS001',
            'nama_indikator' => 'Kualitas kode dan bug rate',
            'kpi' => 'Kualitas kode dan bug rate',
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'weight' => 34,
        ]);
        \DB::table('kpi_hard_skills')->where('id', $hardSkill->id)->update(['id' => 1]);
        $hardSkill->refresh(); // reload so $hardSkill->id === 1

        // Create assessment with self and atasan scores
        $assessment = KpiAssessment::create([
            'user_id' => $employee->id_user,
            'kpi_period_id' => $period->id,
            'atasan_id' => $supervisor->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 75.0,
        ]);

        // Self scores
        KpiAssessmentScore::create([
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'soft_skill',
            'skill_id' => $softSkill->id, // = 1
            'penilai_type' => 'karyawan',
            'skor' => 80,
        ]);
        KpiAssessmentScore::create([
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'hard_skill',
            'skill_id' => $hardSkill->id, // = 1
            'penilai_type' => 'karyawan',
            'skor' => 70,
        ]);

        // Atasan scores
        KpiAssessmentScore::create([
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'soft_skill',
            'skill_id' => $softSkill->id, // = 1
            'penilai_type' => 'atasan',
            'skor' => 75,
        ]);
        KpiAssessmentScore::create([
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'hard_skill',
            'skill_id' => $hardSkill->id, // = 1
            'penilai_type' => 'atasan',
            'skor' => 70,
        ]);

        // Act
        $response = $this->actingAs($supervisor)
            ->get(route('kpi.assessment.show', $assessment->id));

        // Assert — page loads
        $response->assertOk();

        // Assert — no "Unknown" anywhere on the page
        $response->assertDontSee('Unknown');

        // Assert — Self Assessment section shows correct names and types
        $response->assertSee('Commitment');       // soft skill name
        $response->assertSee('Kualitas kode dan bug rate'); // hard skill name
        $response->assertSee('80');               // soft skill self score
        $response->assertSee('70');               // hard skill self score

        // Assert — Penilaian Atasan section shows correct names
        $response->assertSee('Commitment');
        $response->assertSee('Kualitas kode dan bug rate');
        $response->assertSee('75');               // soft skill atasan score
        $response->assertSee('70');               // hard skill atasan atasan score

        // Assert — Comparison table shows correct names and types
        $response->assertSee('Commitment');
        $response->assertSeeText('Soft Skill');   // Soft Skill label in comparison table
        $response->assertSee('Kualitas kode dan bug rate');
        $response->assertSeeText('Hard Skill');  // Hard Skill label in comparison table
    }

    /**
     * Verify that soft skill names are NOT replaced by hard skill names
     * when both share the same skill_id.
     */
    public function test_soft_skill_name_never_appears_under_hard_skill_type(): void
    {
        $period = $this->createPeriod();

        $supervisor = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $employee = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $supervisor->id_user,
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'status' => true,
        ]);

        // Force id=1 for soft skill
        $softSkill = KpiSoftSkill::create(['kode' => 'SS-FORCE', 'nama_indikator' => 'Communication']);
        \DB::table('kpi_soft_skills')->where('id', $softSkill->id)->update(['id' => 1]);
        $softSkill->refresh();

        // Force id=1 for hard skill
        $hardSkill = KpiHardSkill::create([
            'kode' => 'HS-FORCE',
            'nama_indikator' => 'Dokumentasi teknis',
            'kpi' => 'Dokumentasi teknis',
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'weight' => 33,
        ]);
        \DB::table('kpi_hard_skills')->where('id', $hardSkill->id)->update(['id' => 1]);
        $hardSkill->refresh();

        $assessment = KpiAssessment::create([
            'user_id' => $employee->id_user,
            'kpi_period_id' => $period->id,
            'atasan_id' => $supervisor->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 75.0,
        ]);

        KpiAssessmentScore::create([
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'soft_skill',
            'skill_id' => 1,
            'penilai_type' => 'karyawan',
            'skor' => 85,
        ]);
        KpiAssessmentScore::create([
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'hard_skill',
            'skill_id' => 1,
            'penilai_type' => 'karyawan',
            'skor' => 65,
        ]);
        KpiAssessmentScore::create([
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'soft_skill',
            'skill_id' => 1,
            'penilai_type' => 'atasan',
            'skor' => 80,
        ]);
        KpiAssessmentScore::create([
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'hard_skill',
            'skill_id' => 1,
            'penilai_type' => 'atasan',
            'skor' => 70,
        ]);

        $response = $this->actingAs($supervisor)
            ->get(route('kpi.assessment.show', $assessment->id));

        $response->assertOk();

        // "Communication" (soft) must appear; "Dokumentasi teknis" (hard) must appear
        $response->assertSee('Communication');
        $response->assertSee('Dokumentasi teknis');

        // The soft skill name must NOT appear in a Hard Skill context
        // If "Communication" appears under the hard skill label, the bug exists
        // We check that the hard skill row contains "Dokumentasi teknis", not "Communication"
        $this->assertStringNotContainsString(
            'Communication',
            $this->getHardSkillTableRow($response, 'Dokumentasi teknis'),
            'Soft skill name "Communication" appeared in a Hard Skill table row — names are swapped!'
        );

        // The hard skill name must NOT appear in a Soft Skill context
        $this->assertStringNotContainsString(
            'Dokumentasi teknis',
            $this->getSoftSkillTableRow($response, 'Communication'),
            'Hard skill name "Dokumentasi teknis" appeared in a Soft Skill table row — names are swapped!'
        );
    }

    /**
     * Verify no "Unknown" is displayed when scores exist.
     */
    public function test_detail_page_has_no_unknown_when_scores_exist(): void
    {
        $period = $this->createPeriod();

        $supervisor = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $employee = User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $supervisor->id_user,
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'status' => true,
        ]);

        $softSkill = KpiSoftSkill::create(['kode' => 'SS-UNK', 'nama_indikator' => 'Reliable']);
        $hardSkill = KpiHardSkill::create([
            'kode' => 'HS-UNK',
            'nama_indikator' => 'Ketepatan waktu',
            'kpi' => 'Ketepatan waktu',
            'divisi' => 'IT',
            'jabatan' => 'programer',
            'weight' => 50,
        ]);

        $assessment = KpiAssessment::create([
            'user_id' => $employee->id_user,
            'kpi_period_id' => $period->id,
            'atasan_id' => $supervisor->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => 75.0,
        ]);

        KpiAssessmentScore::create([
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'soft_skill',
            'skill_id' => $softSkill->id,
            'penilai_type' => 'karyawan',
            'skor' => 80,
        ]);
        KpiAssessmentScore::create([
            'kpi_assessment_id' => $assessment->id,
            'skill_type' => 'hard_skill',
            'skill_id' => $hardSkill->id,
            'penilai_type' => 'atasan',
            'skor' => 70,
        ]);

        $response = $this->actingAs($supervisor)
            ->get(route('kpi.assessment.show', $assessment->id));

        $response->assertOk();
        // Must not contain the literal string "Unknown" anywhere
        $this->assertStringNotContainsString('Unknown', $response->getContent());
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Extract the table row containing the given hard skill name text.
     * Looks for a <tr> that contains the skill name AND "Hard Skill" label text (not just the name alone).
     * Uses a greedy-match-safe approach: finds the skill name first, then searches backward/forward
     * for the nearest <tr> and </tr> boundaries.
     */
    private function getHardSkillTableRow(\Illuminate\Testing\TestResponse $response, string $skillName): string
    {
        $html = $response->getContent();
        // Find the position of the skill name in the HTML
        $pos = strpos($html, $skillName);
        if ($pos === false) {
            return '';
        }

        // Find the nearest <tr> before the skill name
        $trOpenStart = strrpos(substr($html, 0, $pos), '<tr');
        if ($trOpenStart === false) {
            return '';
        }

        // Find the </tr> after the skill name
        $trCloseEnd = strpos($html, '</tr>', $pos);
        if ($trCloseEnd === false) {
            return '';
        }

        $row = substr($html, $trOpenStart, $trCloseEnd - $trOpenStart + 5);

        // Verify this row also contains "Hard Skill" (not just the skill name)
        if (strpos($row, 'Hard Skill') !== false) {
            return $row;
        }
        return '';
    }

    /**
     * Extract the table row containing the given soft skill name text.
     * Looks for a <tr> that contains the skill name AND "Soft Skill" label text.
     */
    private function getSoftSkillTableRow(\Illuminate\Testing\TestResponse $response, string $skillName): string
    {
        $html = $response->getContent();
        $pos = strpos($html, $skillName);
        if ($pos === false) {
            return '';
        }

        $trOpenStart = strrpos(substr($html, 0, $pos), '<tr');
        if ($trOpenStart === false) {
            return '';
        }

        $trCloseEnd = strpos($html, '</tr>', $pos);
        if ($trCloseEnd === false) {
            return '';
        }

        $row = substr($html, $trOpenStart, $trCloseEnd - $trOpenStart + 5);

        if (strpos($row, 'Soft Skill') !== false) {
            return $row;
        }
        return '';
    }
}
