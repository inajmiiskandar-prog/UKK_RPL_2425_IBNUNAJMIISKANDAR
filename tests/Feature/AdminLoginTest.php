<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

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
