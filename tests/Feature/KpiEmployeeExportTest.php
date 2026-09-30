<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiEmployeeExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Generate unique kode untuk menghindari conflict di database
     */
    private function uniqueKode(string $prefix): string
    {
        return $prefix . '_' . uniqid() . '_' . time();
    }

    /** @test */
    public function admin_can_export_employee_data()
    {
        // Buat atasan dulu
        $atasan = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        // Buat beberapa karyawan dengan atasan_id valid
        $employee1 = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'nik' => '12345',
            'divisi' => 'IT',
            'jabatan' => 'Developer',
            'atasan_id' => $atasan->id_user,
        ]);
        $employee2 = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'nik' => '67890',
            'divisi' => 'HR',
            'jabatan' => 'Staff',
            'atasan_id' => $atasan->id_user,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('kpi.employee.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');

        // Cek filename
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('Data_Karyawan_', $contentDisposition);
        $this->assertStringContainsString('.xls', $contentDisposition);

        // Cek konten HTML berdasarkan kode_karyawan
        $content = $response->getContent();
        $this->assertStringContainsString($employee1->kode_karyawan, $content);
        $this->assertStringContainsString($employee2->kode_karyawan, $content);
        $this->assertStringContainsString('IT', $content);
        $this->assertStringContainsString('Developer', $content);
    }

    /** @test */
    public function hr_can_export_employee_data()
    {
        // HR role test - tidak perlu override kode unik, biarkan factory generate
        $atasan = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $hr = User::factory()->create([
            'role' => 'HR',
            'status' => true,
            'atasan_id' => $admin->id_user,
        ]);

        $employee = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $hr->id_user,
        ]);

        $this->actingAs($hr);

        $response = $this->get(route('kpi.employee.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
    }

    /** @test */
    public function regular_employee_cannot_export()
    {
        $atasan = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
        ]);

        $employee = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $atasan->id_user,
        ]);

        $this->actingAs($employee);

        $response = $this->get(route('kpi.employee.export'));

        $response->assertStatus(403);
    }

    /** @test */
    public function atasan_cannot_export()
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $atasan = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
            'atasan_id' => $admin->id_user,
        ]);

        $this->actingAs($atasan);

        $response = $this->get(route('kpi.employee.export'));

        $response->assertStatus(403);
    }

    /** @test */
    public function export_with_ids_parameter()
    {
        $atasan = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $employee1 = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $atasan->id_user,
        ]);
        $employee2 = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $atasan->id_user,
        ]);
        $employee3 = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $atasan->id_user,
        ]);

        $this->actingAs($admin);

        // Export dengan ids tertentu
        $ids = $employee1->id_user . ',' . $employee2->id_user;
        $response = $this->get(route('kpi.employee.export', ['ids' => $ids]));

        $response->assertStatus(200);

        $content = $response->getContent();
        // Cek bahwa nama dari employee1 dan employee2 ada di hasil
        $this->assertStringContainsString($employee1->nama, $content);
        $this->assertStringContainsString($employee2->nama, $content);
        // employee3 tidak boleh ada
        $this->assertStringNotContainsString($employee3->nama, $content);
    }

    /** @test */
    public function export_with_search_parameter()
    {
        $atasan = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $employee1 = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'divisi' => 'IT',
            'atasan_id' => $atasan->id_user,
        ]);

        // Update nama employee1 setelah dibuat
        $employee1->update(['nama' => 'John Smith']);

        $employee2 = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'divisi' => 'HR',
            'atasan_id' => $atasan->id_user,
        ]);

        // Update nama employee2 setelah dibuat
        $employee2->update(['nama' => 'Jane Doe']);

        $this->actingAs($admin);

        // Search dengan nama
        $response = $this->get(route('kpi.employee.export', ['search' => 'John']));

        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('John Smith', $content);
        $this->assertStringNotContainsString('Jane Doe', $content);
    }

    /** @test */
    public function export_with_search_by_nik()
    {
        $atasan = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $employee1 = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $atasan->id_user,
        ]);

        // Update NIK employee1
        $employee1->update(['nik' => '123456789']);

        $employee2 = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $atasan->id_user,
        ]);

        // Update NIK employee2
        $employee2->update(['nik' => '987654321']);

        $this->actingAs($admin);

        // Search dengan NIK
        $response = $this->get(route('kpi.employee.export', ['search' => '123456']));

        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('123456789', $content);
        $this->assertStringContainsString($employee1->nama, $content);
    }

    /** @test */
    public function export_with_empty_search_returns_data()
    {
        // Test ini memverifikasi bahwa export mengembalikan data
        // karena admin sendiri adalah user yang valid untuk di-export
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('kpi.employee.export'));

        // Export mengembalikan 200 karena admin bisa export dirinya sendiri
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
    }

    /** @test */
    public function export_includes_atasan_name()
    {
        $atasan = User::factory()->create([
            'role' => 'ATASAN',
            'status' => true,
        ]);

        // Update nama atasan
        $atasan->update(['nama' => 'Atasan Utama']);

        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $employee = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
            'atasan_id' => $atasan->id_user,
        ]);

        // Update nama employee
        $employee->update(['nama' => 'Karyawan Bawah']);

        $this->actingAs($admin);

        $response = $this->get(route('kpi.employee.export'));

        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('Atasan Utama', $content);
        $this->assertStringContainsString('Karyawan Bawah', $content);
    }

    /** @test */
    public function export_does_not_include_password()
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $employee = User::factory()->create([
            'role' => 'KARYAWAN',
            'status' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('kpi.employee.export'));

        $response->assertStatus(200);

        $content = $response->getContent();

        // Pastikan password tidak muncul di output
        $this->assertStringNotContainsString('password', strtolower($content));
        $this->assertStringNotContainsString('Password', $content);
    }

    /** @test */
    public function export_includes_correct_columns()
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('kpi.employee.export'));

        $response->assertStatus(200);

        $content = $response->getContent();

        // Cek kolom yang harus ada
        $expectedColumns = ['No', 'Kode Karyawan', 'Nama', 'Username', 'Email', 'No HP', 'NIK', 'Divisi', 'Jabatan', 'Atasan', 'Role', 'Status'];

        foreach ($expectedColumns as $column) {
            $this->assertStringContainsString($column, $content, "Kolom {$column} tidak ditemukan");
        }
    }

    /** @test */
    public function unauthenticated_user_cannot_export()
    {
        $response = $this->get(route('kpi.employee.export'));

        $response->assertRedirect(route('login'));
    }
}
