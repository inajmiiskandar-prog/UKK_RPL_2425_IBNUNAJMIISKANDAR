<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_autocomplete_off_on_username(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response->assertSee('autocomplete="off"', false);
    }

    public function test_login_page_has_autocomplete_new_password_on_password(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response->assertSee('autocomplete="new-password"', false);
    }

    public function test_admin_can_login_with_default_seeded_credentials(): void
    {
        $this->seed(UserSeeder::class);

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => '123456',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        $this->assertSame('admin', auth()->user()->username);
        $this->assertSame('ADMIN', auth()->user()->role);
    }
}
