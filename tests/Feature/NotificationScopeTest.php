<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NotificationScopeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Non-admin tidak melihat deskripsi/nama aktivitas user lain
     */
    public function test_non_admin_does_not_see_other_users_notification_description(): void
    {
        $user1 = User::factory()->create(['role' => 'SALES', 'status' => true]);
        $user2 = User::factory()->create(['role' => 'TEKNISI', 'status' => true]);

        // User1 bikin aktivitas
        DB::table('activity_logs')->insert([
            'type' => 'TEST_ACTIVITY',
            'description' => 'Aktivitas User1',
            'id_user' => $user1->id_user,
            'createdAt' => now(),
        ]);

        // User2 bikin aktivitas
        DB::table('activity_logs')->insert([
            'type' => 'TEST_ACTIVITY',
            'description' => 'Aktivitas User2 yang rahasia',
            'id_user' => $user2->id_user,
            'createdAt' => now()->subSecond(),
        ]);

        // Login sebagai user1, buka dashboard
        $response = $this->actingAs($user1)->get(route('kpi.dashboard'));
        $response->assertStatus(200);

        // User1 harus melihat aktivitasnya sendiri
        $response->assertSee('Aktivitas User1');

        // User1 TIDAK boleh melihat aktivitas user2
        $response->assertDontSee('Aktivitas User2 yang rahasia');
        $response->assertDontSee($user2->nama);
    }

    /**
     * Non-admin mendapat badge untuk aktivitasnya sendiri
     */
    public function test_non_admin_gets_badge_for_own_activities(): void
    {
        $user = User::factory()->create(['role' => 'SALES', 'status' => true]);

        // User bikin aktivitas
        DB::table('activity_logs')->insert([
            'type' => 'TEST_ACTIVITY',
            'description' => 'Aktivitas Test User',
            'id_user' => $user->id_user,
            'createdAt' => now(),
        ]);

        // Login sebagai user, buka dashboard
        $response = $this->actingAs($user)->get(route('kpi.dashboard'));
        $response->assertStatus(200);

        // Badge harus muncul untuk 1 aktivitas unread
        $response->assertSee('notificationBadge');
        $response->assertSee('1', false); // Badge dengan angka 1
    }

    /**
     * ADMIN melihat aktivitas user lain
     */
    public function test_admin_sees_other_users_notifications(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $user = User::factory()->create(['role' => 'SALES', 'status' => true]);

        // User bikin aktivitas
        DB::table('activity_logs')->insert([
            'type' => 'TEST_ACTIVITY',
            'description' => 'Aktivitas Rahasia User Lain',
            'id_user' => $user->id_user,
            'createdAt' => now(),
        ]);

        // Admin login, buka dashboard
        $response = $this->actingAs($admin)->get(route('kpi.dashboard'));
        $response->assertStatus(200);

        // ADMIN boleh melihat aktivitas user lain
        $response->assertSee('Aktivitas Rahasia User Lain');
        $response->assertSee($user->nama);

        // Badge harus muncul
        $response->assertSee('notificationBadge');
    }
}
