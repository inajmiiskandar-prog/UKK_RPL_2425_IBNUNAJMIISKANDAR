<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_groups_have_toggle_buttons_with_aria_expanded(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);

        $response = $this->actingAs($admin)->get(route('profile.edit'));

        $response->assertOk();

        // ADMIN punya 4 grup: Master Data, Transaksi, Lainnya, KPI, Pengaturan
        // Semua heading grup jadi tombol toggle dengan aria-expanded
        $response->assertSee('data-sidebar-toggle="master-data"', false);
        $response->assertSee('data-sidebar-toggle="transaksi"', false);
        $response->assertSee('data-sidebar-toggle="lainnya"', false);
        $response->assertSee('data-sidebar-toggle="kpi"', false);
        $response->assertSee('data-sidebar-toggle="pengaturan"', false);

        // Semua tombol toggle punya aria-expanded
        $response->assertSee('aria-expanded="false"', false);
    }

    public function test_active_group_is_expanded_on_page_load(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);

        // Kunjungi halaman Master Data Area
        $response = $this->actingAs($admin)->get(route('masterdata.area.index'));

        $response->assertOk();

        // Grup Master Data harus terbuka (aria-expanded="true")
        $response->assertSee('data-sidebar-toggle="master-data"', false);
        // assertSee dengan false = literal match, tapi aria-expanded ada di markup
        $html = $response->getContent();
        $this->assertStringContainsString('data-sidebar-toggle="master-data"', $html);
        $this->assertStringContainsString('aria-expanded="true"', $html);
    }

    public function test_group_without_allowed_links_is_not_rendered(): void
    {
        // SALES tidak punya akses Master Data
        $sales = User::factory()->create(['role' => 'SALES', 'status' => true]);

        $response = $this->actingAs($sales)->get(route('profile.edit'));

        $response->assertOk();

        // SALES tidak punya grup Master Data, Lainnya, atau Pengaturan
        $response->assertDontSee('data-sidebar-toggle="master-data"', false);
        $response->assertDontSee('data-sidebar-toggle="lainnya"', false);
        $response->assertDontSee('data-sidebar-toggle="pengaturan"', false);

        // Tapi punya Transaksi (FAB) dan KPI
        $response->assertSee('data-sidebar-toggle="transaksi"', false);
        $response->assertSee('data-sidebar-toggle="kpi"', false);
    }

    public function test_logistik_has_no_transaction_group(): void
    {
        $logistik = User::factory()->create(['role' => 'LOGISTIK', 'status' => true]);

        $response = $this->actingAs($logistik)->get(route('profile.edit'));

        $response->assertOk();

        // LOGISTIK tidak punya Transaksi
        $response->assertDontSee('data-sidebar-toggle="transaksi"', false);

        // Tapi punya Master Data, KPI (tanpa Lainnya/Pengaturan)
        $response->assertSee('data-sidebar-toggle="master-data"', false);
        $response->assertSee('data-sidebar-toggle="kpi"', false);
        $response->assertDontSee('data-sidebar-toggle="lainnya"', false);
        $response->assertDontSee('data-sidebar-toggle="pengaturan"', false);
    }

    public function test_sidebar_links_remain_in_dom_when_group_closed(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);

        $response = $this->actingAs($admin)->get(route('profile.edit'));

        $response->assertOk();

        // Semua URL link tetap ada di DOM
        $response->assertSee(route('masterdata.area.index'), false);
        $response->assertSee(route('masterdata.pop.index'), false);
        $response->assertSee(route('masterdata.olt.index'), false);
        $response->assertSee(route('masterdata.odp.index'), false);
        $response->assertSee(route('masterdata.ont.index'), false);
        $response->assertSee(route('masterdata.port-pon.index'), false);
        $response->assertSee(route('masterdata.material.index'), false);
        $response->assertSee(route('masterdata.paket.index'), false);
        $response->assertSee(route('jaringan.fab.index'), false);
        $response->assertSee(route('jaringan.baa.index'), false);
        $response->assertSee(route('users.index'), false);
        $response->assertSee(route('kpi.assessment.index'), false);
        $response->assertSee(route('settings.index'), false);
    }

    public function test_all_roles_see_correct_groups(): void
    {
        $roles = ['ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK'];

        foreach ($roles as $role) {
            $user = User::factory()->create(['role' => $role, 'status' => true]);

            $response = $this->actingAs($user)->get(route('profile.edit'));
            $response->assertOk();

            // Semua role punya grup KPI
            $response->assertSee('data-sidebar-toggle="kpi"', false);

            // Transaksi: ADMIN, LEADER, SALES, TEKNISI (bukan LOGISTIK)
            if ($role !== 'LOGISTIK') {
                $response->assertSee('data-sidebar-toggle="transaksi"', false);
            } else {
                $response->assertDontSee('data-sidebar-toggle="transaksi"', false);
            }

            // Master Data: ADMIN (semua), LEADER (olt,odp), TEKNISI (ont,port-pon,material), LOGISTIK (ont,material,paket)
            // SALES tidak punya Master Data
            if ($role === 'SALES') {
                $response->assertDontSee('data-sidebar-toggle="master-data"', false);
            } else {
                $response->assertSee('data-sidebar-toggle="master-data"', false);
            }

            // Lainnya & Pengaturan: hanya ADMIN
            if ($role === 'ADMIN') {
                $response->assertSee('data-sidebar-toggle="lainnya"', false);
                $response->assertSee('data-sidebar-toggle="pengaturan"', false);
            } else {
                $response->assertDontSee('data-sidebar-toggle="lainnya"', false);
                $response->assertDontSee('data-sidebar-toggle="pengaturan"', false);
            }
        }
    }
}
