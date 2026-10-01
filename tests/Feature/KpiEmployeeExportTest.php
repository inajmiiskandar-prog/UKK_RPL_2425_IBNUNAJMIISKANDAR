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
        $leader = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        // Buat beberapa karyawan dengan atasan_id valid
        $teknisi1 = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'nik' => '12345',
            'divisi' => 'IT',
            'jabatan' => 'Developer',
            'atasan_id' => $leader->id_user,
        ]);
        $teknisi2 = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'nik' => '67890',
            'divisi' => 'HR',
            'jabatan' => 'Staff',
            'atasan_id' => $leader->id_user,
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
        $this->assertStringContainsString($teknisi1->kode_karyawan, $content);
        $this->assertStringContainsString($teknisi2->kode_karyawan, $content);
        $this->assertStringContainsString('IT', $content);
        $this->assertStringContainsString('Developer', $content);
    }

    /** @test */
    public function leader_cannot_export_employee_data()
    {
        $leader = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        $this->actingAs($leader);

        $response = $this->get(route('kpi.employee.export'));

        // LEADER tidak memiliki akses export (hanya ADMIN)
        $response->assertStatus(403);
    }

    /** @test */
    public function sales_cannot_export_employee_data()
    {
        $leader = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);

        $sales = User::factory()->create([
            'role' => 'SALES',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        $this->actingAs($sales);

        $response = $this->get(route('kpi.employee.export'));

        $response->assertStatus(403);
    }

    /** @test */
    public function teknisi_cannot_export_employee_data()
    {
        $leader = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);

        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        $this->actingAs($teknisi);

        $response = $this->get(route('kpi.employee.export'));

        $response->assertStatus(403);
    }

    /** @test */
    public function logistik_cannot_export_employee_data()
    {
        $leader = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);

        $logistik = User::factory()->create([
            'role' => 'LOGISTIK',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        $this->actingAs($logistik);

        $response = $this->get(route('kpi.employee.export'));

        $response->assertStatus(403);
    }

    /** @test */
    public function export_with_ids_parameter()
    {
        $leader = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $teknisi1 = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);
        $teknisi2 = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);
        $teknisi3 = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        $this->actingAs($admin);

        // Export dengan ids tertentu
        $ids = $teknisi1->id_user . ',' . $teknisi2->id_user;
        $response = $this->get(route('kpi.employee.export', ['ids' => $ids]));

        $response->assertStatus(200);

        $content = $response->getContent();
        // Cek bahwa nama dari teknisi1 dan teknisi2 ada di hasil
        $this->assertStringContainsString($teknisi1->nama, $content);
        $this->assertStringContainsString($teknisi2->nama, $content);
        // teknisi3 tidak boleh ada
        $this->assertStringNotContainsString($teknisi3->nama, $content);
    }

    /** @test */
    public function export_with_search_parameter()
    {
        $leader = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $teknisi1 = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'divisi' => 'IT',
            'atasan_id' => $leader->id_user,
        ]);

        // Update nama teknisi1 setelah dibuat
        $teknisi1->update(['nama' => 'John Smith']);

        $teknisi2 = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'divisi' => 'HR',
            'atasan_id' => $leader->id_user,
        ]);

        // Update nama teknisi2 setelah dibuat
        $teknisi2->update(['nama' => 'Jane Doe']);

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
        $leader = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $teknisi1 = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        // Update NIK teknisi1
        $teknisi1->update(['nik' => '123456789']);

        $teknisi2 = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        // Update NIK teknisi2
        $teknisi2->update(['nik' => '987654321']);

        $this->actingAs($admin);

        // Search dengan NIK
        $response = $this->get(route('kpi.employee.export', ['search' => '123456']));

        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('123456789', $content);
        $this->assertStringContainsString($teknisi1->nama, $content);
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
        $leader = User::factory()->create([
            'role' => 'LEADER',
            'status' => true,
        ]);

        // Update nama leader
        $leader->update(['nama' => 'Leader Utama']);

        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
            'status' => true,
            'atasan_id' => $leader->id_user,
        ]);

        // Update nama teknisi
        $teknisi->update(['nama' => 'Teknisi Bawah']);

        $this->actingAs($admin);

        $response = $this->get(route('kpi.employee.export'));

        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('Leader Utama', $content);
        $this->assertStringContainsString('Teknisi Bawah', $content);
    }

    /** @test */
    public function export_does_not_include_password()
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => true,
        ]);

        $teknisi = User::factory()->create([
            'role' => 'TEKNISI',
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
