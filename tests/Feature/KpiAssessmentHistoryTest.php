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

    public function test_non_admin_sees_own_and_direct_subordinates_assessments(): void
    {
        $leader = $this->createUser(['role' => 'LEADER', 'status' => true]);
        $employee = $this->createUser([
            'nama' => 'Karyawan Bawahan', 'name' => 'Karyawan Bawahan',
            'role' => 'TEKNISI', 'status' => true,
            'atasan_id' => $leader->id_user,
        ]);
        $otherEmployee = $this->createUser([
            'nama' => 'Bukan Bawahan', 'name' => 'Bukan Bawahan',
            'role' => 'SALES', 'status' => true,
        ]);
        $period = $this->createPeriod();
        $this->createAssessment($leader, $supervisor = $this->createUser(['role' => 'ADMIN', 'status' => true]), $period, 75);
        $this->createAssessment($employee, $leader, $period, 85);
        $this->createAssessment($otherEmployee, $supervisor = $this->createUser(['role' => 'ADMIN', 'status' => true]), $period, 99);

        // Leader melihat assessment DIRI SENDIRI + BAWAHAN LANGSUNG
        $response = $this->actingAs($leader)->get(route('kpi.assessment.history'));

        $response->assertOk()
            ->assertSeeText('Karyawan Bawahan')
            ->assertSeeText('85.0')
            ->assertDontSeeText('99.0'); // Skor bukan bawahan tidak boleh terlihat

        // Leader juga melihat assessment dirinya sendiri
        $response->assertSeeText('75.0');

        // Jika leader filter ke user_id bawahan, hanya lihat bawahan
        $responseBawahan = $this->actingAs($leader)->get(route('kpi.assessment.history', [
            'user_id' => $employee->id_user,
        ]));
        $responseBawahan->assertOk()
            ->assertSeeText('Karyawan Bawahan')
            ->assertSeeText('85.0');

        // Jika leader coba akses user_id orang lain (bukan bawahan), tetap lihat diri+bawahan
        $responseOthers = $this->actingAs($leader)->get(route('kpi.assessment.history', [
            'user_id' => $otherEmployee->id_user,
        ]));
        $responseOthers->assertOk()
            ->assertDontSeeText('99.0'); // Skor orang lain tidak bocor
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

    public function test_period_filter_works_and_never_exposes_unauthorized_users(): void
    {
        $supervisor = $this->createUser(['role' => 'LEADER', 'status' => true]);
        $employee = $this->createUser([
            'nama' => 'Pemilik Histori', 'name' => 'Pemilik Histori', 'role' => 'TEKNISI', 'status' => true,
        ]);
        $otherEmployee = $this->createUser([
            'nama' => 'Bukan Milik Sendiri', 'name' => 'Bukan Milik Sendiri', 'role' => 'SALES', 'status' => true,
        ]);
        $selectedPeriod = $this->createPeriod('Periode Pilihan');
        $otherPeriod = $this->createPeriod('Periode Tidak Dipilih');
        $this->createAssessment($employee, $supervisor, $selectedPeriod, 45);
        $this->createAssessment($employee, $supervisor, $otherPeriod, 75);
        $this->createAssessment($otherEmployee, $supervisor, $selectedPeriod, 99);

        // Employee mengakses dengan user_id orang lain (di luar scope)
        $response = $this->actingAs($employee)->get(route('kpi.assessment.history', [
            'user_id' => $otherEmployee->id_user,
            'period_id' => $selectedPeriod->id,
        ]));

        $response->assertOk()
            ->assertViewHas('selectedPeriod', fn ($period) => $period?->id === $selectedPeriod->id)
            ->assertSee('name="period_id"', false)
            ->assertSeeText('Pemilik Histori')
            ->assertSeeText('45.0')
            ->assertDontSeeText('75.0') // Period lain tidak boleh terlihat
            ->assertDontSeeText('99.0') // Orang lain tidak boleh terlihat
            ->assertDontSeeText('Bukan Milik Sendiri');
    }

    public function test_admin_sees_all_assessments_by_default(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN', 'status' => true]);
        $supervisor = $this->createUser(['role' => 'LEADER', 'status' => true]);
        $user1 = $this->createUser(['nama' => 'User Satu', 'role' => 'TEKNISI', 'status' => true]);
        $user2 = $this->createUser(['nama' => 'User Dua', 'role' => 'SALES', 'status' => true]);
        $period = $this->createPeriod();
        $this->createAssessment($user1, $supervisor, $period, 70);
        $this->createAssessment($user2, $supervisor, $period, 85);

        $response = $this->actingAs($admin)->get(route('kpi.assessment.history'));

        $response->assertOk();
        // Admin dengan null $targetUser (melihat semua user)
        $response->assertViewHas('targetUser', null);
        // Ada 2 assessment
        $response->assertViewHas('assessments', function ($assessments) {
            return $assessments->count() === 2;
        });
    }

    public function test_admin_can_filter_to_specific_employee(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN', 'status' => true]);
        $supervisor = $this->createUser(['role' => 'LEADER', 'status' => true]);
        $user1 = $this->createUser(['nama' => 'User Target', 'role' => 'TEKNISI', 'status' => true]);
        $user2 = $this->createUser(['nama' => 'User Lain', 'role' => 'SALES', 'status' => true]);
        $period = $this->createPeriod();
        $this->createAssessment($user1, $supervisor, $period, 70);
        $this->createAssessment($user2, $supervisor, $period, 85);

        $response = $this->actingAs($admin)->get(route('kpi.assessment.history', [
            'user_id' => $user1->id_user,
        ]));

        $response->assertOk();
        // Admin dengan user_id spesifik punya $targetUser
        $response->assertViewHas('targetUser', function ($user) use ($user1) {
            return $user && $user->id_user === $user1->id_user;
        });
        // Hanya 1 assessment
        $response->assertViewHas('assessments', function ($assessments) {
            return $assessments->count() === 1;
        });
    }

    public function test_subordinate_cannot_see_atasan_assessments(): void
    {
        $atasan = $this->createUser(['role' => 'LEADER', 'status' => true]);
        $bawahan = $this->createUser([
            'nama' => 'Bawahan Test', 'role' => 'TEKNISI', 'status' => true,
            'atasan_id' => $atasan->id_user,
        ]);
        $period = $this->createPeriod();
        $this->createAssessment($atasan, $supervisor = $this->createUser(['role' => 'ADMIN', 'status' => true]), $period, 90);
        $this->createAssessment($bawahan, $atasan, $period, 75);

        // Bawahan mengakses history
        $response = $this->actingAs($bawahan)->get(route('kpi.assessment.history'));

        $response->assertOk();
        // Bawahan hanya lihat 1 assessment (dirinya sendiri)
        $response->assertViewHas('assessments', function ($assessments) {
            return $assessments->count() === 1;
        });
        // Assessment milik bawahan
        $response->assertViewHas('assessments', function ($assessments) use ($bawahan) {
            return $assessments->first()->user_id === $bawahan->id_user;
        });
    }

    public function test_atasan_with_bawahan_sees_multi_user_mode_without_filter(): void
    {
        $atasan = $this->createUser(['role' => 'LEADER', 'status' => true]);
        $bawahan = $this->createUser([
            'nama' => 'Bawahan Test', 'role' => 'TEKNISI', 'status' => true,
            'atasan_id' => $atasan->id_user,
        ]);
        $period = $this->createPeriod();
        $this->createAssessment($bawahan, $atasan, $period, 75);

        $response = $this->actingAs($atasan)->get(route('kpi.assessment.history'));

        $response->assertOk();
        // Multi-user mode: $targetUser = null
        $response->assertViewHas('targetUser', null);
        // Ada 1 assessment
        $response->assertViewHas('assessments', function ($assessments) {
            return $assessments->count() === 1;
        });
    }

    public function test_admin_sees_inactive_employee_assessment(): void
    {
        $admin = $this->createUser(['role' => 'ADMIN', 'status' => true]);
        $supervisor = $this->createUser(['role' => 'LEADER', 'status' => true]);
        $inactiveUser = $this->createUser([
            'nama' => 'User Nonaktif', 'role' => 'TEKNISI', 'status' => false, // NONAKTIF
        ]);
        $period = $this->createPeriod();
        $this->createAssessment($inactiveUser, $supervisor, $period, 80);

        $response = $this->actingAs($admin)->get(route('kpi.assessment.history'));

        $response->assertOk();
        // ADMIN melihat assessment user nonaktif
        $response->assertViewHas('assessments', function ($assessments) {
            return $assessments->count() === 1;
        });
    }

    public function test_supervisor_sees_inactive_subordinate_assessment(): void
    {
        $supervisor = $this->createUser(['role' => 'LEADER', 'status' => true]);
        $inactiveBawahan = $this->createUser([
            'nama' => 'Bawahan Nonaktif', 'role' => 'TEKNISI', 'status' => false, // NONAKTIF
            'atasan_id' => $supervisor->id_user,
        ]);
        $period = $this->createPeriod();
        $this->createAssessment($inactiveBawahan, $supervisor, $period, 70);

        $response = $this->actingAs($supervisor)->get(route('kpi.assessment.history'));

        $response->assertOk();
        // Supervisor melihat assessment bawahan nonaktif
        $response->assertViewHas('assessments', function ($assessments) use ($inactiveBawahan) {
            return $assessments->where('user_id', $inactiveBawahan->id_user)->count() === 1;
        });
    }

    public function test_user_id_outside_scope_is_ignored(): void
    {
        $user1 = $this->createUser(['role' => 'TEKNISI', 'status' => true]);
        $randomUser = $this->createUser(['nama' => 'Random User', 'role' => 'SALES', 'status' => true]);
        $period = $this->createPeriod();
        $this->createAssessment($user1, $supervisor = $this->createUser(['role' => 'LEADER', 'status' => true]), $period, 80);
        $this->createAssessment($randomUser, $supervisor = $this->createUser(['role' => 'LEADER', 'status' => true]), $period, 95);

        // User1 mencoba akses user_id orang lain yang bukan bawahan
        $response = $this->actingAs($user1)->get(route('kpi.assessment.history', [
            'user_id' => $randomUser->id_user,
        ]));

        $response->assertOk()
            // Tidak bocor data random user
            ->assertDontSeeText('Random User')
            ->assertDontSeeText('95.0');
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