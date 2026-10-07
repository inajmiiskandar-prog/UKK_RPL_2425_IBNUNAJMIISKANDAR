<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Baa;
use App\Models\BaaDetail;
use App\Models\Fab;
use App\Models\Material;
use App\Models\Odp;
use App\Models\Olt;
use App\Models\Ont;
use App\Models\Paket;
use App\Models\Pop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaaDestroyOntStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper untuk membuat fixture BAA lengkap dengan relasi.
     */
    private function createBaaFixture(User $user): array
    {
        $suffix = substr(uniqid('', true), -8);

        // Setup Area, Paket, FAB
        $area = Area::create([
            'kode_area' => 'AR' . $suffix,
            'nama_area' => 'Area Test',
        ]);
        $paket = Paket::create([
            'kode_paket' => 'PK' . $suffix,
            'nama_paket' => 'Paket Test',
            'kecepatan' => '20 Mbps',
            'harga' => 20000,
        ]);
        $fab = Fab::create([
            'kode_fab' => 'FAB' . $suffix,
            'nama_pelanggan' => 'Pelanggan Test',
            'nik' => 'NIK' . $suffix,
            'no_hp' => '0800000000',
            'alamat' => 'Alamat Test',
            'latitude' => 0,
            'longitude' => 0,
            'status' => 'AKTIF',
            'id_area' => $area->id_area,
            'id_paket' => $paket->id_paket,
            'id_user' => $user->id_user,
            'id_penginput' => $user->id_user,
        ]);

        // Setup POP, OLT, ODP, ONT
        $pop = Pop::create([
            'kode_pop' => 'POP' . $suffix,
            'nama_pop' => 'POP Test',
            'alamat' => 'Alamat POP Test',
            'latitude' => 0,
            'longitude' => 0,
            'id_area' => $area->id_area,
        ]);
        $olt = Olt::create([
            'kode_olt' => 'OLT' . $suffix,
            'nama_olt' => 'OLT Test',
            'lokasi' => 'Lokasi OLT Test',
            'latitude' => 0,
            'longitude' => 0,
            'id_pop' => $pop->id_pop,
        ]);
        $odp = Odp::create([
            'kode_odp' => 'ODP' . $suffix,
            'nama_odp' => 'ODP Test',
            'alamat' => 'Alamat ODP Test',
            'latitude' => 0,
            'longitude' => 0,
            'jumlah_port' => 8,
            'stok_port' => 8,
            'id_olt' => $olt->id_olt,
        ]);
        $ont = Ont::create([
            'serial_number' => 'ONT' . $suffix,
            'pelanggan' => 'Pelanggan ONT Test',
            'status' => 'TERPASANG',
            'id_pop' => $pop->id_pop,
            'id_odp' => $odp->id_odp,
        ]);

        // Setup Material
        $material = Material::create([
            'kode_material' => 'MAT' . $suffix,
            'nama_material' => 'Material Test',
            'stok' => 100,
            'minimal_stok' => 10,
            'satuan' => 'pcs',
            'harga' => 5000,
            'kondisi' => 'BAIK',
        ]);

        // Setup BAA
        $baa = Baa::create([
            'kode_baa' => 'BAA' . $suffix,
            'tanggal_instalasi' => '2026-01-01 10:00:00',
            'status' => 'SELESAI',
            'id_fab' => $fab->id_fab,
            'id_user' => $user->id_user,
            'id_olt' => $olt->id_olt,
            'id_ont' => $ont->id_ont,
            'id_odp' => $odp->id_odp,
            'port_olt' => 1,
        ]);

        // Setup BaaDetail (material yang digunakan saat instalasi)
        BaaDetail::create([
            'id_baa' => $baa->id_baa,
            'id_material' => $material->id_material,
            'jumlah' => 5,
        ]);

        return compact('fab', 'ont', 'material', 'baa');
    }

    /**
     * Test: ADMIN menghapus BAA, BAA terhapus dan ONT status kembali 'TERSEDIA'.
     * Ini memastikan typo 'TERSDIA' sudah diperbaiki.
     */
    public function test_admin_can_delete_baa_and_ont_status_returns_to_tersedia(): void
    {
        // Create ADMIN user
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => true]);

        // Create BAA fixture
        $fixtures = $this->createBaaFixture($admin);
        $ontId = $fixtures['ont']->id_ont;
        $fabId = $fixtures['fab']->id_fab;
        $baaId = $fixtures['baa']->id_baa;
        $materialId = $fixtures['material']->id_material;
        $stokAwal = $fixtures['material']->stok; // 100

        // Verifikasi kondisi awal
        $this->assertDatabaseHas('ont', ['id_ont' => $ontId, 'status' => 'TERPASANG']);
        $this->assertDatabaseHas('fab', ['id_fab' => $fabId, 'status' => 'AKTIF']);

        // ADMIN deletes BAA
        $response = $this->actingAs($admin)
            ->delete(route('jaringan.baa.destroy', $baaId));

        $response->assertRedirect(route('jaringan.baa.index'));
        $response->assertSessionHas('success', 'BAA berhasil dihapus!');

        // BAA harus terhapus
        $this->assertDatabaseMissing('baa', ['id_baa' => $baaId]);

        // ONT status harus kembali ke 'TERSEDIA' (bukan 'TERSDIA')
        $this->assertDatabaseHas('ont', ['id_ont' => $ontId, 'status' => 'TERSEDIA']);

        // FAB status harus kembali ke 'OPEN'
        $this->assertDatabaseHas('fab', ['id_fab' => $fabId, 'status' => 'OPEN']);

        // Material stock harus dikembalikan
        $this->assertDatabaseHas('material', [
            'id_material' => $materialId,
            'stok' => $stokAwal + 5, // +5 dari BaaDetail
        ]);
    }
}
