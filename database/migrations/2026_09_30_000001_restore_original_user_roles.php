<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Kembalikan role ke 5 role PassOne: ADMIN, LEADER, SALES, TEKNISI, LOGISTIK
     *
     * Pemetaan:
     * - KARYAWAN → TEKNISI (sesuai keputusan user)
     * - ATASAN → LEADER (atasan ditentukan oleh relasi users.atasan_id)
     * - HR → ADMIN (HR ditentukan oleh atasan_id atau data lain, bukan role)
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // 1. UPDATE data user dulu sebelum mempersempit enum
        DB::statement("UPDATE users SET role = 'TEKNISI' WHERE role = 'KARYAWAN'");
        DB::statement("UPDATE users SET role = 'LEADER' WHERE role = 'ATASAN'");
        DB::statement("UPDATE users SET role = 'ADMIN' WHERE role = 'HR'");

        // 2. Persempit enum ke 5 role PassOne
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK') NOT NULL DEFAULT 'SALES'");
    }

    /**
     * Kembalikan enum ke 7 nilai lama
     *
     * Catatan: Pemetaan per user TIDAK BISA dikembalikan otomatis karena informasi
     * role asli (KARYAWAN, ATASAN, HR) sudah tertimpa. Perlu restore dari backup
     * atau migrasi manual jika perlu rollback.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Kembalikan lebar enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK', 'ATASAN', 'KARYAWAN', 'HR') NOT NULL DEFAULT 'SALES'");

        // Catatan: role user tidak dikembalikan karena mapping sudah hilang
    }
};
