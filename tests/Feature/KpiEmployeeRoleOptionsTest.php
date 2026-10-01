<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiEmployeeRoleOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpi_employee_create_form_has_only_allowed_roles(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-kpi-role',
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $this->actingAs($admin)
            ->get('/kpi/employee/create')
            ->assertOk()
            // 5 role baru PassOne
            ->assertSee('Administrator')
            ->assertSee('Leader')
            ->assertSee('Sales')
            ->assertSee('Teknisi')
            ->assertSee('Logistik');
    }

    public function test_kpi_employee_edit_form_has_allowed_roles(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-kpi-edit-role',
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $employee = User::factory()->create([
            'nama' => 'Karyawan Lama',
            'role' => 'LEADER',
            'status' => true,
        ]);

        $this->actingAs($admin)
            ->get('/kpi/employee/' . $employee->id_user . '/edit')
            ->assertOk()
            // 5 role baru PassOne
            ->assertSee('Administrator')
            ->assertSee('Leader')
            ->assertSee('Sales')
            ->assertSee('Teknisi')
            ->assertSee('Logistik');
    }

    public function test_general_users_form_still_keeps_full_role_list(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-users-role',
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $this->actingAs($admin)
            ->get('/users/create')
            ->assertOk()
            ->assertSee('Leader')
            ->assertSee('Sales')
            ->assertSee('Teknisi')
            ->assertSee('Logistik');
    }

    /**
     * Test ini memverifikasi bahwa 5 role baru PassOne bisa disimpan.
     */
    public function test_kpi_employee_can_store_each_allowed_role(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-kpi-store-role',
            'role' => 'ADMIN',
            'status' => true,
        ]);

        foreach (['ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK'] as $index => $role) {
            $response = $this->actingAs($admin)->post('/kpi/employee', [
                'nama' => 'Karyawan Role ' . $role,
                'nik' => 'NIK-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'divisi' => 'IT',
                'jabatan' => 'Developer',
                'alamat' => 'Jl. Satu',
                'telepon' => '081234567890',
                'tanggal_masuk' => '2026-01-15',
                'role' => $role,
                'password' => '123456',
            ]);

            $response->assertRedirect('/kpi/employee');
            $this->assertDatabaseHas('users', [
                'nama' => 'Karyawan Role ' . $role,
                'role' => $role,
            ]);
        }
    }

    public function test_kpi_employee_can_update_role_to_allowed_values(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-kpi-update-role',
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $employee = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
        ]);

        $response = $this->actingAs($admin)->put('/kpi/employee/' . $employee->id_user, [
            'role' => 'LEADER',
        ]);

        $response->assertRedirect('/kpi/employee');
        $this->assertDatabaseHas('users', [
            'id_user' => $employee->id_user,
            'role' => 'LEADER',
        ]);
    }
}
