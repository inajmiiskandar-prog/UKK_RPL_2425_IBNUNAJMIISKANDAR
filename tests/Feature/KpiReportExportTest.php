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

class KpiReportExportTest extends TestCase
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

    public function test_grade_boundaries_follow_the_requested_ranges(): void
    {
        foreach ([
            [89.99, 'B'],
            [90, 'A'],
            [74.99, 'C'],
            [59.99, 'D'],
            [44.99, 'E'],
            [null, '-'],
        ] as [$score, $expectedGrade]) {
            $assessment = new KpiAssessment(['skor_akhir' => $score]);

            $this->assertSame($expectedGrade, $assessment->grade());
        }
    }

    public function test_assessment_calculates_soft_and_weighted_hard_scores_separately(): void
    {
        $employee = $this->createUser(['role' => 'TEKNISI', 'status' => true]);
        $period = $this->createPeriod();
        $assessment = KpiAssessment::create([
            'user_id' => $employee->id_user,
            'kpi_period_id' => $period->id,
            'status' => 'sudah_dicek',
        ]);

        $softSkillA = KpiSoftSkill::create(['kode' => 'SS-REPORT-A', 'nama_indikator' => 'Komunikasi']);
        $softSkillB = KpiSoftSkill::create(['kode' => 'SS-REPORT-B', 'nama_indikator' => 'Disiplin']);
        $hardSkillA = KpiHardSkill::create([
            'kode' => 'HS-REPORT-A', 'nama_indikator' => 'Kualitas', 'weight' => 25,
        ]);
        $hardSkillB = KpiHardSkill::create([
            'kode' => 'HS-REPORT-B', 'nama_indikator' => 'Produktivitas', 'weight' => 75,
        ]);
        $hardSkillWithoutWeight = KpiHardSkill::create([
            'kode' => 'HS-REPORT-C', 'nama_indikator' => 'Tanpa bobot', 'weight' => null,
        ]);

        $this->createScorePair($assessment, 'soft_skill', $softSkillA->id, 80, 60);
        $this->createScorePair($assessment, 'soft_skill', $softSkillB->id, 60, 80);
        $this->createScorePair($assessment, 'hard_skill', $hardSkillA->id, 80, 60);
        $this->createScorePair($assessment, 'hard_skill', $hardSkillB->id, 40, 60);
        $this->createScorePair($assessment, 'hard_skill', $hardSkillWithoutWeight->id, 100, 100);

        $skillScores = $assessment->calculateSkillScores();

        $this->assertEquals(70, $skillScores['soft_skill']);
        $this->assertEquals(55, $skillScores['hard_skill']);
    }

    public function test_csv_has_exact_report_columns_and_one_complete_data_row(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN', 'status' => true]);
        $supervisor = $this->createUser([
            'nama' => 'Leader Utama', 'name' => 'Leader Utama', 'role' => 'LEADER', 'status' => true,
        ]);
        $employee = $this->createUser([
            'nama' => 'Karyawan Contoh', 'name' => 'Karyawan Contoh', 'nik' => 'NIK-007', 'divisi' => 'IT', 'jabatan' => 'Engineer',
            'role' => 'TEKNISI', 'status' => true,
        ]);
        $period = $this->createPeriod();
        $assessment = $this->createAssessment($employee, $period, 'sudah_dicek', 82.25, $supervisor);
        $softSkill = KpiSoftSkill::create(['kode' => 'SS-CSV', 'nama_indikator' => 'Komunikasi']);
        $hardSkill = KpiHardSkill::create([
            'kode' => 'HS-CSV', 'nama_indikator' => 'Kualitas', 'weight' => 100,
        ]);
        $this->createScorePair($assessment, 'soft_skill', $softSkill->id, 80, 60);
        $this->createScorePair($assessment, 'hard_skill', $hardSkill->id, 90, 80);

        $headers = [
            'No', 'NIK', 'Nama Karyawan', 'Divisi', 'Jabatan', 'Periode',
            'Skor Soft Skill', 'Skor Hard Skill', 'Skor Akhir', 'Grade', 'Status', 'Disetujui Oleh',
        ];
        $csvResponse = $this->actingAs($admin)->get(route('kpi.report.exportCsv', $period->id));
        $csvResponse->assertOk();
        $csvLines = array_values(array_filter(explode("\n", trim($csvResponse->getContent()))));

        $this->assertCount(2, $csvLines);
        $this->assertSame($headers, str_getcsv($csvLines[0]));
        $this->assertSame([
            '1', 'NIK-007', 'Karyawan Contoh', 'IT', 'Engineer', 'Periode Test',
            '70.00', '85.00', '82.25', 'B', 'sudah_dicek', 'Leader Utama',
        ], str_getcsv($csvLines[1]));

        $excelResponse = $this->get(route('kpi.report.exportExcel', $period->id));
        $excelResponse->assertOk();
        $this->assertSame($headers, $this->tableHeadersFromHtml($excelResponse->getContent()));
    }

    public function test_export_only_contains_assessments_from_the_selected_period(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN', 'status' => true]);
        $selectedPeriod = $this->createPeriod();
        $otherPeriod = KpiPeriod::create([
            'nama' => 'Periode Lain',
            'tanggal_mulai' => '2026-02-01',
            'tanggal_selesai' => '2026-02-28',
            'status' => 'selesai',
        ]);
        $selectedEmployee = $this->createUser([
            'nama' => 'Karyawan Terpilih', 'name' => 'Karyawan Terpilih', 'role' => 'TEKNISI', 'status' => true,
        ]);
        $otherEmployee = $this->createUser([
            'nama' => 'Karyawan Periode Lain', 'name' => 'Karyawan Periode Lain', 'role' => 'TEKNISI', 'status' => true,
        ]);
        $this->createAssessment($selectedEmployee, $selectedPeriod, 'selesai', 80);
        $this->createAssessment($otherEmployee, $otherPeriod, 'selesai', 90);

        $response = $this->actingAs($admin)->get(route('kpi.report.exportCsv', $selectedPeriod->id));
        $response->assertOk()->assertSeeText('Karyawan Terpilih')->assertDontSeeText('Karyawan Periode Lain');
    }

    public function test_export_only_contains_sudah_dicek_and_selesai_assessments(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN', 'status' => true]);
        $period = $this->createPeriod();

        foreach (['sudah_dicek', 'selesai', 'pending', 'self_done', 'atasan_done', 'menunggu_review'] as $status) {
            $employee = $this->createUser([
                'nama' => 'Karyawan ' . $status, 'name' => 'Karyawan ' . $status,
                'role' => 'TEKNISI', 'status' => true,
            ]);
            $this->createAssessment($employee, $period, $status, 80);
        }

        $response = $this->actingAs($admin)->get(route('kpi.report.exportCsv', $period->id));
        $response->assertOk();

        foreach (['sudah_dicek', 'selesai'] as $status) {
            $response->assertSeeText('Karyawan ' . $status);
        }
        foreach (['pending', 'self_done', 'atasan_done', 'menunggu_review'] as $status) {
            $response->assertDontSeeText('Karyawan ' . $status);
        }
    }

    public function test_index_and_preview_use_the_same_filtered_report_rows_and_columns(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN', 'status' => true]);
        $period = $this->createPeriod();
        $included = $this->createUser([
            'nama' => 'Masuk Laporan', 'name' => 'Masuk Laporan', 'role' => 'TEKNISI', 'status' => true,
        ]);
        $excluded = $this->createUser([
            'nama' => 'Belum Dinilai', 'name' => 'Belum Dinilai', 'role' => 'TEKNISI', 'status' => true,
        ]);
        $this->createAssessment($included, $period, 'sudah_dicek', 91);
        $this->createAssessment($excluded, $period, 'pending', null);
        $expectedHeaders = [
            'No', 'NIK', 'Nama Karyawan', 'Divisi', 'Jabatan', 'Periode',
            'Skor Soft Skill', 'Skor Hard Skill', 'Skor Akhir', 'Grade', 'Status', 'Disetujui Oleh',
        ];

        $indexResponse = $this->actingAs($admin)->get(route('kpi.report.index', ['period_id' => $period->id]));
        $previewResponse = $this->get(route('kpi.report.preview', ['period_id' => $period->id]));

        foreach ([$indexResponse, $previewResponse] as $response) {
            $response->assertOk()->assertSeeText('Masuk Laporan')->assertDontSeeText('Belum Dinilai');
            $this->assertSame($expectedHeaders, $this->tableHeadersFromHtml($response->getContent()));
        }
    }

    // These URLs mirror the route() expressions used by the report download buttons.
    public function test_admin_can_download_csv_from_button_url(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN', 'status' => true]);
        $period = $this->createPeriod();
        $url = route('kpi.report.exportCsv', $period->id);

        $response = $this->actingAs($admin)->get($url);

        $response->assertOk();
        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_admin_can_download_excel_from_button_url(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN', 'status' => true]);
        $period = $this->createPeriod();
        $url = route('kpi.report.exportExcel', $period->id);

        $response = $this->actingAs($admin)->get($url);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
    }

    public function test_non_admin_roles_cannot_download_csv_or_excel(): void
    {
        $period = $this->createPeriod();
        $urls = [
            route('kpi.report.exportCsv', $period->id),
            route('kpi.report.exportExcel', $period->id),
        ];

        foreach (['LEADER', 'SALES', 'TEKNISI', 'LOGISTIK'] as $role) {
            $user = $this->createUser(['role' => $role, 'status' => true]);

            foreach ($urls as $url) {
                $this->actingAs($user)->get($url)->assertForbidden();
            }
        }
    }

    public function test_guest_is_redirected_to_login_from_csv_or_excel(): void
    {
        $period = $this->createPeriod();
        $urls = [
            route('kpi.report.exportCsv', $period->id),
            route('kpi.report.exportExcel', $period->id),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }
}