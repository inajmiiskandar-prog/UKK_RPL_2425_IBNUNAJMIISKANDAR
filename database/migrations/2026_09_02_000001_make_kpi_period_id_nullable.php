<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Membuat kpi_period_id nullable di kpi_assessments
     * Karena wizard penilaian KPI tidak lagi menggunakan periode,
     * melainkan langsung mencatat berdasarkan created_at
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Langkah 1: Drop foreign key yang mereferensikan kpi_period_id (jika ada)
        try {
            DB::statement("ALTER TABLE kpi_assessments DROP FOREIGN KEY kpi_assessments_kpi_period_id_foreign");
        } catch (\Exception $e) {
            // Foreign key mungkin tidak ada, lanjutkan
        }

        // Langkah 2: Drop unique constraint
        try {
            DB::statement("ALTER TABLE kpi_assessments DROP INDEX kpi_assessments_user_id_kpi_period_id_unique");
        } catch (\Exception $e) {
            // Unique constraint mungkin tidak ada, lanjutkan
        }

        // Langkah 3: Drop index lama di kpi_period_id (jika ada)
        try {
            DB::statement("ALTER TABLE kpi_assessments DROP INDEX kpi_assessments_kpi_period_id_foreign");
        } catch (\Exception $e) {
            // Index mungkin tidak ada, lanjutkan
        }

        // Langkah 4: Buat kolom nullable
        DB::statement("ALTER TABLE kpi_assessments MODIFY COLUMN kpi_period_id BIGINT UNSIGNED NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Kembalikan kolom ke NOT NULL
        DB::statement("ALTER TABLE kpi_assessments MODIFY COLUMN kpi_period_id BIGINT UNSIGNED NOT NULL");

        // Kembalikan unique constraint
        DB::statement("ALTER TABLE kpi_assessments ADD UNIQUE INDEX kpi_assessments_user_id_kpi_period_id_unique (user_id, kpi_period_id)");
    }
};
