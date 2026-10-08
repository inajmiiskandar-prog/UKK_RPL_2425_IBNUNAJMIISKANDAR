<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiAssessmentIndexActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_employee_rating_wizard_link(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($admin)
            ->get(route('kpi.assessment.index'))
            ->assertOk()
            ->assertSee(route('kpi.assessment.create'), false)
            ->assertSeeText('Nilai Karyawan');
    }

    public function test_user_with_direct_subordinate_sees_employee_rating_wizard_link(): void
    {
        $leader = User::factory()->create(['role' => 'LEADER']);
        User::factory()->create([
            'role' => 'TEKNISI',
            'atasan_id' => $leader->id_user,
        ]);

        $this->actingAs($leader)
            ->get(route('kpi.assessment.index'))
            ->assertOk()
            ->assertSee(route('kpi.assessment.create'), false)
            ->assertSeeText('Nilai Karyawan');
    }

    public function test_teknisi_without_direct_subordinate_does_not_see_employee_rating_wizard_link(): void
    {
        $teknisi = User::factory()->create(['role' => 'TEKNISI']);

        $this->actingAs($teknisi)
            ->get(route('kpi.assessment.index'))
            ->assertOk()
            ->assertDontSee(route('kpi.assessment.create'), false)
            ->assertDontSeeText('Nilai Karyawan');
    }
}