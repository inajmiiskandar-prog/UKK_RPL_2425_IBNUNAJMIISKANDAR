<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambahkan role baru yang dibutuhkan untuk KPI tanpa menghapus role lama
     * seperti Leader, Sales, Teknisi, dan Logistik yang sudah dipakai sistem.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK', 'ATASAN', 'KARYAWAN', 'HR') DEFAULT 'SALES'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK', 'ATASAN') DEFAULT 'SALES'");
    }
};
