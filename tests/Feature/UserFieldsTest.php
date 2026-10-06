<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_with_nik()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'John Doe',
            'username' => 'johndoe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'SALES',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
            'nik' => '12345678',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'nama' => 'John Doe',
            'nik' => '12345678',
        ]);
    }

    public function test_user_can_store_with_divisi_jabatan()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'Jane Doe',
            'username' => 'janedoe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'TEKNISI',
            'status' => '1',
            'jkl' => 'PEREMPUAN',
            'divisi' => 'IT',
            'jabatan' => 'Developer',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'nama' => 'Jane Doe',
            'divisi' => 'IT',
            'jabatan' => 'Developer',
        ]);
    }

    public function test_user_can_store_with_alamat()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'Bob Smith',
            'username' => 'bobsmith',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'LOGISTIK',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
            'alamat' => 'Jl. Sudirman No. 123, Jakarta',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'nama' => 'Bob Smith',
            'alamat' => 'Jl. Sudirman No. 123, Jakarta',
        ]);
    }

    public function test_user_can_store_with_tanggal_masuk()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'Alice Wong',
            'username' => 'alicewong',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'SALES',
            'status' => '1',
            'jkl' => 'PEREMPUAN',
            'tanggal_masuk' => '2026-01-15',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'nama' => 'Alice Wong',
            'tanggal_masuk' => '2026-01-15',
            'join_date' => '2026-01-15',
        ]);
    }

    public function test_telepon_fills_both_no_hp_and_no_telp_columns()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'Test User',
            'username' => 'testuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'TEKNISI',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
            'telepon' => '081234567890',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'nama' => 'Test User',
            'no_hp' => '081234567890',
            'no_telp' => '081234567890',
        ]);
    }

    public function test_user_can_update_profile_fields_and_atasan_id()
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $leader = User::factory()->create(['role' => 'LEADER', 'status' => true]);
        $employee = User::factory()->create([
            'nama' => 'Old Employee',
            'username' => 'oldemployee',
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => null,
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $employee->id_user), [
            'nama' => 'Updated Employee',
            'username' => 'updatedemployee',
            'role' => 'TEKNISI',
            'status' => '1',
            'jkl' => 'PEREMPUAN',
            'nik' => '99887766',
            'divisi' => 'IT',
            'jabatan' => 'Developer',
            'alamat' => 'Jl. Baru No. 10',
            'telepon' => '081122334455',
            'tanggal_masuk' => '2026-02-10',
            'atasan_id' => $leader->id_user,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id_user' => $employee->id_user,
            'nama' => 'Updated Employee',
            'username' => 'updatedemployee',
            'nik' => '99887766',
            'divisi' => 'IT',
            'jabatan' => 'Developer',
            'alamat' => 'Jl. Baru No. 10',
            'no_hp' => '081122334455',
            'no_telp' => '081122334455',
            'tanggal_masuk' => '2026-02-10',
            'join_date' => '2026-02-10',
            'atasan_id' => $leader->id_user,
        ]);
    }

    // Keep edit-password behavior aligned with the controller's confirmed rule.
    public function test_user_can_update_password_with_matching_confirmation()
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $employee = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $employee->id_user), [
            'nama' => $employee->nama,
            'username' => $employee->username,
            'role' => $employee->role,
            'status' => '1',
            'jkl' => $employee->jkl,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertTrue(Hash::check('new-password-123', $employee->fresh()->password));
    }

    public function test_user_cannot_update_password_with_mismatched_confirmation()
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $employee = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $employee->id_user), [
            'nama' => $employee->nama,
            'username' => $employee->username,
            'role' => $employee->role,
            'status' => '1',
            'jkl' => $employee->jkl,
            'password' => 'new-password-123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('old-password', $employee->fresh()->password));
    }

    public function test_blank_password_keeps_existing_password_unchanged()
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $employee = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $employee->id_user), [
            'nama' => $employee->nama,
            'username' => $employee->username,
            'role' => $employee->role,
            'status' => '1',
            'jkl' => $employee->jkl,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertTrue(Hash::check('old-password', $employee->fresh()->password));
    }

    public function test_user_cannot_set_self_as_atasan_on_store_or_update()
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);
        $employee = User::factory()->create([
            'nama' => 'Self Atasan',
            'username' => 'selfatasan',
            'role' => 'SALES',
            'status' => true,
        ]);

        $storeResponse = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'Has Self Atasan',
            'username' => 'hasselfatasan',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'TEKNISI',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
            'atasan_id' => $employee->id_user,
        ]);

        $this->assertTrue($storeResponse->isRedirect());

        $updateResponse = $this->actingAs($admin)->put(route('users.update', $employee->id_user), [
            'nama' => $employee->nama,
            'username' => $employee->username,
            'role' => 'TEKNISI',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
            'atasan_id' => $employee->id_user,
        ]);

        $this->assertTrue($updateResponse->isRedirect());
        $this->assertDatabaseMissing('users', ['id_user' => $employee->id_user, 'atasan_id' => $employee->id_user]);
    }

    public function test_kode_user_has_3_digit_format()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'New Employee',
            'username' => 'newemp',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'SALES',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::where('username', 'newemp')->first();
        $this->assertNotNull($user->kode_user);
        $this->assertMatchesRegularExpression('/^USR\d{3}$/', $user->kode_user);
    }

    public function test_kode_karyawan_auto_generated_with_emp_prefix()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'New Employee',
            'username' => 'newemp',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'SALES',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::where('username', 'newemp')->first();
        $this->assertNotNull($user->kode_karyawan);
        $this->assertStringStartsWith('EMP', $user->kode_karyawan);
    }

    public function test_email_auto_generated_when_empty()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'Auto Email User',
            'username' => 'autoemail',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'SALES',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
            // email field empty
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::where('username', 'autoemail')->first();
        $this->assertStringEndsWith('@passnet.local', $user->email);
    }

    public function test_email_from_input_when_provided()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'Custom Email User',
            'username' => 'customemail',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'SALES',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
            'email' => 'custom@example.com',
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::where('username', 'customemail')->first();
        $this->assertEquals('custom@example.com', $user->email);
    }

    public function test_user_can_store_each_allowed_role_ADMIN()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'Admin User',
            'username' => 'adminuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'ADMIN',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['nama' => 'Admin User', 'role' => 'ADMIN']);
    }

    public function test_user_can_store_each_allowed_role_LEADER()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'Leader User',
            'username' => 'leaderuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'LEADER',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['nama' => 'Leader User', 'role' => 'LEADER']);
    }

    public function test_user_can_store_each_allowed_role_SALES()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'Sales User',
            'username' => 'salesuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'SALES',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['nama' => 'Sales User', 'role' => 'SALES']);
    }

    public function test_user_can_store_each_allowed_role_TEKNISI()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'Teknisi User',
            'username' => 'teknisiuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'TEKNISI',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['nama' => 'Teknisi User', 'role' => 'TEKNISI']);
    }

    public function test_user_can_store_each_allowed_role_LOGISTIK()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'Logistik User',
            'username' => 'logistikuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'LOGISTIK',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['nama' => 'Logistik User', 'role' => 'LOGISTIK']);
    }

    public function test_employee_status_set_to_active_on_create()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'nama' => 'Active Status User',
            'username' => 'activestatus',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'SALES',
            'status' => '1',
            'jkl' => 'LAKI_LAKI',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'nama' => 'Active Status User',
            'employee_status' => 'Active',
        ]);
    }
}
