<?php

namespace Tests\Feature;

use App\Models\KpiPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the "Kembali" (back) button on KPI Assessment pages.
 * Verifies that history and recap pages have a back button pointing to the
 * Penilaian KPI index page.
 */
class KpiAssessmentBackButtonTest extends TestCase
{
    use RefreshDatabase;

    private function createPeriod(): KpiPeriod
    {
        return KpiPeriod::create([
            'nama' => 'KPI Januari 2025',
            'tanggal_mulai' => now()->startOfMonth(),
            'tanggal_selesai' => now()->endOfMonth(),
            'status' => 'aktif',
        ]);
    }

    public function test_history_page_has_back_button_to_index(): void
    {
        $user = User::factory()->create(['role' => 'TEKNISI', 'status' => true]);
        $this->createPeriod();

        $response = $this->actingAs($user)
            ->get(route('kpi.assessment.history'));

        $response->assertOk();
        // Back button exists with correct href
        $response->assertSee('href="' . route('kpi.assessment.index') . '"', false);
        // Back button text
        $response->assertSee('Kembali');
    }

    public function test_recap_page_has_back_button_to_index(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $this->createPeriod();

        $response = $this->actingAs($user)
            ->get(route('kpi.assessment.recap'));

        $response->assertOk();
        // Back button exists with correct href
        $response->assertSee('href="' . route('kpi.assessment.index') . '"', false);
        // Back button text
        $response->assertSee('Kembali');
    }
}
