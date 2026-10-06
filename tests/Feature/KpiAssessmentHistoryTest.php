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

class KpiAssessmentHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $attributes): User
    {
        /** @var User $user */
        $user = User::factory()->create($attributes);

        return $user;
    }

    private function createPeriod(string $name = 'Periode Histori'): KpiPeriod
    {
        return KpiPeriod::create([
            'nama' => $name,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-01-31',
            'status' => 'selesai',
        ]);
    }

    private function createAssessment(User $employee, User $supervisor, KpiPeriod $period, float $score): KpiAssessment
    {
        return KpiAssessment::create([
            'user_id' => $employee->id_user,
            'kpi_period_id' => $period->id,
            'atasan_id' => $supervisor->id_user,
            'status' => 'sudah_dicek',
            'skor_akhir' => $score,
        ]);
    }

    private function createScorePair(KpiAssessment $assessment, string $skillType, int $skillId, int $score): void
    {
        foreach (['karyawan', 'atasan'] as $reviewerType) {
            KpiAssessmentScore::create([
                'kpi_assessment_id' => $assessment->id,
                'skill_type' => $skillType,
                'skill_id' => $skillId,
                'penilai_type' => $reviewerType,
                'skor' => $score,
            ]);
        }
    }

    public function test_history_shows_model_scores_grade_supervisor_date_and_detail_link(): void
    {
        $supervisor = $this->createUser([
            'nama' => 'Atasan Histori', 'name' => 'Atasan Histori', 'role' => 'LEADER', 'status' => true,
        ]);
        $employee = $this->createUser([
            'nama' => 'Karyawan Histori', 'name' => 'Karyawan Histori', 'role' => 'TEKNISI', 'status' => true,
            'atasan_id' => $supervisor->id_user,
        ]);
        $period = $this->createPeriod();
        $assessment = $this->createAssessment($employee, $supervisor, $period, 95);
        $softSkill = KpiSoftSkill::create([
            'kode' => 'SS-HISTORY', 'nama_indikator' => 'Komunikasi',
        ]);
        $hardSkill = KpiHardSkill::create([
            'kode' => 'HS-HISTORY', 'nama_indikator' => 'Kualitas',
            'kpi' => 'Kualitas', 'weight' => 100,
        ]);
        $this->createScorePair($assessment, 'soft_skill', $softSkill->id, 88);
        $this->createScorePair($assessment, 'hard_skill', $hardSkill->id, 76);

        $response = $this->actingAs($employee)->get(route('kpi.assessment.history'));

        $response->assertOk()
            ->assertSeeText('Skor Soft Skill')
            ->assertSeeText('Skor Hard Skill')
            ->assertSeeText('Skor Akhir')
            ->assertSeeText('Grade')
            ->assertSeeText('Nama Atasan')
            ->assertSeeText('Atasan Histori')
            ->assertSeeText('88.0')
            ->assertSeeText('76.0')
            ->assertSeeText('A')
            ->assertSeeText($assessment->created_at->format('d M Y'))
            ->assertSeeText('Sudah selesai dinilai')
            ->assertSee(route('kpi.assessment.show', $assessment->id), false)
            ->assertSeeText('Detail')
            ->assertSee('ondblclick=', false)
            ->assertSee('overflow-x-auto', false)
            ->assertSee('dark:bg-green-900/40 dark:text-green-300', false);
    }

    public function test_non_admin_history_remains_limited_to_own_assessments(): void
    {
        $supervisor = $this->createUser(['role' => 'LEADER', 'status' => true]);
        $employee = $this->createUser([
            'nama' => 'Histori Milik Saya', 'name' => 'Histori Milik Saya',
            'role' => 'TEKNISI', 'status' => true,
        ]);
        $otherEmployee = $this->createUser([
            'nama' => 'Histori Orang Lain', 'name' => 'Histori Orang Lain',
            'role' => 'SALES', 'status' => true,
        ]);
        $period = $this->createPeriod();
        $this->createAssessment($employee, $supervisor, $period, 65);
        $this->createAssessment($otherEmployee, $supervisor, $period, 99);

        $response = $this->actingAs($employee)->get(route('kpi.assessment.history', [
            'user_id' => $otherEmployee->id_user,
        ]));

        $response->assertOk()
            ->assertSeeText('Histori Milik Saya')
            ->assertDontSeeText('Histori Orang Lain')
            ->assertSeeText('65.0')
            ->assertDontSeeText('99.0');
    }

    public function test_history_paginates_and_accepts_only_supported_page_sizes(): void
    {
        $supervisor = $this->createUser(['role' => 'LEADER', 'status' => true]);
        $employee = $this->createUser(['role' => 'TEKNISI', 'status' => true]);

        for ($index = 1; $index <= 23; $index++) {
            $period = $this->createPeriod('Periode ' . $index);
            $this->createAssessment($employee, $supervisor, $period, 60 + $index);
        }

        $response = $this->actingAs($employee)->get(route('kpi.assessment.history', [
            'user_id' => $employee->id_user,
            'per_page' => 25,
        ]));

        $response->assertOk()
            ->assertViewHas('assessments', fn ($assessments) => $assessments->perPage() === 25
                && $assessments->total() === 23
                && $assessments->count() === 23)
            ->assertSee('name="per_page"', false)
            ->assertSee('value="10"', false)
            ->assertSee('value="25" selected', false)
            ->assertSee('value="50"', false)
            ->assertSee('value="100"', false)
            ->assertSeeText('Menampilkan 1-23 dari 23');

        $invalidPageSizeResponse = $this->get(route('kpi.assessment.history', [
            'per_page' => 999,
        ]));
        $invalidPageSizeResponse->assertViewHas('assessments', fn ($assessments) => $assessments->perPage() === 10
            && $assessments->total() === 23
            && $assessments->count() === 10)
            ->assertSee('value="10" selected', false)
            ->assertSeeText('Menampilkan 1-10 dari 23');
    }

    public function test_history_pagination_links_keep_query_string(): void
    {
        $supervisor = $this->createUser(['role' => 'LEADER', 'status' => true]);
        $employee = $this->createUser(['role' => 'TEKNISI', 'status' => true]);
        for ($index = 1; $index <= 12; $index++) {
            $period = $this->createPeriod('Page Link Period ' . $index);
            $this->createAssessment($employee, $supervisor, $period, 70);
        }

        $response = $this->actingAs($employee)->get(route('kpi.assessment.history', [
            'user_id' => $employee->id_user,
            'per_page' => 10,
        ]));

        $response->assertOk()->assertViewHas('assessments', function ($assessments) use ($employee) {
            parse_str((string) parse_url($assessments->url(2), PHP_URL_QUERY), $query);

            return ($query['page'] ?? null) == 2
                && ($query['per_page'] ?? null) == 10
                && ($query['user_id'] ?? null) == $employee->id_user;
        });
    }

    public function test_period_filter_is_available_to_non_admin_and_never_exposes_other_users(): void
    {
        $supervisor = $this->createUser(['role' => 'LEADER', 'status' => true]);
        $employee = $this->createUser([
            'nama' => 'Pemilik Histori', 'name' => 'Pemilik Histori', 'role' => 'TEKNISI', 'status' => true,
        ]);
        $otherEmployee = $this->createUser([
            'nama' => 'Bukan Pemilik', 'name' => 'Bukan Pemilik', 'role' => 'SALES', 'status' => true,
        ]);
        $selectedPeriod = $this->createPeriod('Periode Pilihan');
        $otherPeriod = $this->createPeriod('Periode Tidak Dipilih');
        $this->createAssessment($employee, $supervisor, $selectedPeriod, 45);
        $this->createAssessment($employee, $supervisor, $otherPeriod, 75);
        $this->createAssessment($otherEmployee, $supervisor, $selectedPeriod, 99);

        $response = $this->actingAs($employee)->get(route('kpi.assessment.history', [
            'user_id' => $otherEmployee->id_user,
            'period_id' => $selectedPeriod->id,
        ]));

        $response->assertOk()
            ->assertViewHas('selectedPeriod', fn ($period) => $period?->id === $selectedPeriod->id)
            ->assertSee('name="period_id"', false)
            ->assertSeeText('Pemilik Histori')
            ->assertSeeText('45.0')
            ->assertDontSeeText('75.0')
            ->assertDontSeeText('99.0')
            ->assertDontSeeText('Bukan Pemilik');
    }

    public function test_invalid_period_filter_is_ignored(): void
    {
        $supervisor = $this->createUser(['role' => 'LEADER', 'status' => true]);
        $employee = $this->createUser(['role' => 'TEKNISI', 'status' => true]);
        $period = $this->createPeriod();
        $this->createAssessment($employee, $supervisor, $period, 83);

        $response = $this->actingAs($employee)->get(route('kpi.assessment.history', [
            'period_id' => 'not-a-period',
        ]));

        $response->assertOk()
            ->assertViewHas('selectedPeriod', null)
            ->assertSeeText('83.0');
    }
}