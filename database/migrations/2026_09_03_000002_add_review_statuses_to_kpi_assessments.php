<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE kpi_assessments MODIFY COLUMN status ENUM('pending', 'self_done', 'atasan_done', 'selesai', 'menunggu_review', 'sudah_dicek') DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE kpi_assessments MODIFY COLUMN status ENUM('pending', 'self_done', 'atasan_done', 'selesai') DEFAULT 'pending'");
    }
};
