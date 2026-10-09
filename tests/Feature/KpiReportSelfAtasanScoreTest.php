<?php

namespace Tests\Feature;

use App\Models\KpiPeriod;
use App\Models\KpiAssessment;
use App\Models\KpiAssessmentScore;
use App\Models\KpiHardSkill;
use App\Models\KpiSoftSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiReportSelfAtasanScoreTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $attributes): User
    {
        /** @var User $user */
        $user = User::factory()->create($attributes);

        return $user;
    }

    private function createPeriod(): KpiPeriod
    {
        return KpiPeriod::create([
            'nama' => 'Periode Test',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-01-31',
            'status' => 'selesai',
        ]);
    }

    private function createScorePair(KpiAssessment $assessment, string $skillType, int $skillId, int $employeeScore, int $supervisorScore): void
    {
        foreach (['karyawan' => $employeeScore, 'atasan' => $supervisorScore] as $reviewerType => $score) {
            KpiAssessmentScore::create([
                'kpi_assessment_id' => $assessment->id,
                'skill_type' => $skillType,
                'skill_id' => $skillId,
                'penilai_type' => $reviewerType,
                'skor' => $score,
            ]);
        }
    }

    private function createAssessment(User $employee, KpiPeriod $period, string $status, ?float $score, ?User $supervisor = null): KpiAssessment
    {
        return KpiAssessment::create([
            'user_id' => $employee->id_user,
            'kpi_period_id' => $period->id,
            'atasan_id' => $supervisor?->id_user,
            'status' => $status,
            'skor_akhir' => $score,
        ]);
    }

    private function tableHeadersFromHtml(string $html): array
    {
        preg_match_all('/<th[^>]*>(.*?)<\/th>/s', $html, $matches);

        return array_map(
            fn ($header) => trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($header), ENT_QUOTES, 'UTF-8'))),
            $matches[1]
        );
    }

    /**
     * Helper: buat 2 soft skill + 2 hard skill, tiap skill punya skor self & atasan berbeda.
     * softSkillA: self=80, atasan=60
     * softSkillB: self=70, atasan=90
     * hardSkillA (weight=25): self=60, atasan=80
     * hardSkillB (weight=75): self=90, atasan=70
     *
     * Skor Self = avg(soft_self=[80,70]=75) + weighted(hard_self=[60,90]=[25,75]) = (75 + 82.5) / 2 = 78.75
     * Skor Atasan = avg(soft_atasan=[60,90]=75) + weighted(hard_atasan=[80,70]=[25,75]) = (75 + 72.5) / 2 = 73.75
     */
    private function createFourSkillAssessment(User $employee, KpiPeriod $period, User $supervisor): KpiAssessment
    {
        $assessment = $this->createAssessment($employee, $period, 'sudah_dicek', null, $supervisor);

        $softA = KpiSoftSkill::create(['kode' => 'SS-A', 'nama_indikator' => 'Komunikasi']);
        $softB = KpiSoftSkill::create(['kode' => 'SS-B', 'nama_indikator' => 'Disiplin']);
        $hardA = KpiHardSkill::create(['kode' => 'HS-A', 'nama_indikator' => 'Teknis', 'weight' => 25]);
        $hardB = KpiHardSkill::create(['kode' => 'HS-B', 'nama_indikator' => 'Produktivitas', 'weight' => 75]);

        $this->createScorePair($assessment, 'soft_skill', $softA->id, 80, 60);
        $this->createScorePair($assessment, 'soft_skill', $softB->id, 70, 90);
        $this->createScorePair($assessment, 'hard_skill', $hardA->id, 60, 80);
        $this->createScorePair($assessment, 'hard_skill', $hardB->id, 90, 70);

        return $assessment;
    }

    public function test_export_has_14_headers_including_skor_self_and_skor_atasan(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN', 'status' => true]);
        $period = $this->createPeriod();

        $expectedHeaders = [
            'No', 'NIK', 'Nama Karyawan', 'Divisi', 'Jabatan', 'Periode',
            'Skor Soft Skill', 'Skor Hard Skill', 'Skor Self', 'Skor Atasan',
            'Skor Akhir', 'Grade', 'Status', 'Disetujui Oleh',
        ];

        $csvResponse = $this->actingAs($admin)->get(route('kpi.report.exportCsv', $period->id));
        $csvResponse->assertOk();
        $csvLines = array_values(array_filter(explode("\n", trim($csvResponse->getContent()))));

        $this->assertCount(1, $csvLines); // tidak ada data
        $this->assertSame($expectedHeaders, str_getcsv($csvLines[0]));

        $excelResponse = $this->actingAs($admin)->get(route('kpi.report.exportExcel', $period->id));
        $excelResponse->assertOk();
        $this->assertSame($expectedHeaders, $this->tableHeadersFromHtml($excelResponse->getContent()));
    }

    public function test_skor_self_and_atasan_are_correct_for_assessment_with_same_ids(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN', 'status' => true]);
        $supervisor = $this->createUser([
            'nama' => 'Leader', 'name' => 'Leader', 'role' => 'LEADER', 'status' => true,
        ]);
        $employee = $this->createUser([
            'nama' => 'Karyawan Test', 'name' => 'Karyawan Test', 'role' => 'TEKNISI', 'status' => true,
        ]);
        $period = $this->createPeriod();
        $this->createFourSkillAssessment($employee, $period, $supervisor);

        $csvResponse = $this->actingAs($admin)->get(route('kpi.report.exportCsv', $period->id));
        $csvResponse->assertOk();
        $csvLines = array_values(array_filter(explode("\n", trim($csvResponse->getContent()))));

        $this->assertCount(2, $csvLines);
        $row = str_getcsv($csvLines[1]);

        // Skor Soft Skill: ((80+60)/2 + (70+90)/2) / 2 = (70 + 80) / 2 = 75
        $this->assertSame('75.00', $row[6]);
        // Skor Hard Skill: (25*((60+80)/2) + 75*((90+70)/2)) / 100 = (1750 + 6000) / 100 = 77.5
        $this->assertSame('77.50', $row[7]);
        // Skor Self: ((80+70)/2 + (25*60+75*90)/100) / 2 = (75 + 82.5) / 2 = 78.75
        $this->assertSame('78.75', $row[8]);
        // Skor Atasan: ((60+90)/2 + (25*80+75*70)/100) / 2 = (75 + 72.5) / 2 = 73.75
        $this->assertSame('73.75', $row[9]);
    }

    public function test_skor_self_and_atasan_show_dash_when_no_scores_for_that_penilai(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN', 'status' => true]);
        $supervisor = $this->createUser([
            'nama' => 'Leader', 'name' => 'Leader', 'role' => 'LEADER', 'status' => true,
        ]);
        $employee = $this->createUser([
            'nama' => 'Karyawan Self Only', 'name' => 'Karyawan Self Only', 'role' => 'TEKNISI', 'status' => true,
        ]);
        $period = $this->createPeriod();

        // Assessment: ada self score tapi TIDAK ada atasan score
        $assessment = $this->createAssessment($employee, $period, 'sudah_dicek', null, $supervisor);

        $softSkill = KpiSoftSkill::create(['kode' => 'SS-ONLY', 'nama_indikator' => 'Komunikasi']);
        $hardSkill = KpiHardSkill::create(['kode' => 'HS-ONLY', 'nama_indikator' => 'Teknis', 'weight' => 100]);

        // Hanya skor karyawan (self), tidak ada skor atasan
        foreach (['soft_skill' => $softSkill->id, 'hard_skill' => $hardSkill->id] as $skillType => $skillId) {
            KpiAssessmentScore::create([
                'kpi_assessment_id' => $assessment->id,
                'skill_type' => $skillType,
                'skill_id' => $skillId,
                'penilai_type' => 'karyawan',
                'skor' => 85,
            ]);
        }

        $csvResponse = $this->actingAs($admin)->get(route('kpi.report.exportCsv', $period->id));
        $csvResponse->assertOk();
        $csvLines = array_values(array_filter(explode("\n", trim($csvResponse->getContent()))));

        $this->assertCount(2, $csvLines);
        $row = str_getcsv($csvLines[1]);

        // Karena calculateSkillScores() butuh pasangan self+atasan per skill_id,
        // skor soft/hard/akhir return null → '-'
        $this->assertSame('-', $row[6]); // Skor Soft Skill (gabungan, null)
        $this->assertSame('-', $row[7]); // Skor Hard Skill (gabungan, null)
        $this->assertSame('85.00', $row[8]); // Skor Self (hanya karyawan) → avg soft + weighted hard
        $this->assertSame('-', $row[9]);      // Skor Atasan tidak ada
    }

    public function test_preview_page_shows_skor_self_and_skor_atasan(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN', 'status' => true]);
        $supervisor = $this->createUser([
            'nama' => 'Leader', 'name' => 'Leader', 'role' => 'LEADER', 'status' => true,
        ]);
        $employee = $this->createUser([
            'nama' => 'Karyawan Preview', 'name' => 'Karyawan Preview', 'role' => 'TEKNISI', 'status' => true,
        ]);
        $period = $this->createPeriod();
        $this->createFourSkillAssessment($employee, $period, $supervisor);

        $response = $this->actingAs($admin)->get(route('kpi.report.preview', ['period_id' => $period->id]));
        $response->assertOk();

        // Cek header ada
        $this->assertStringContainsString('Skor Self', $response->getContent());
        $this->assertStringContainsString('Skor Atasan', $response->getContent());
        // Cek nilai
        $this->assertStringContainsString('78.75', $response->getContent());
        $this->assertStringContainsString('73.75', $response->getContent());
    }
}
