<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE activity_logs MODIFY COLUMN type VARCHAR(255) NOT NULL");
        }
    }

    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE activity_logs MODIFY COLUMN type ENUM(
                'USER_CREATED', 'USER_UPDATED', 'USER_DELETED', 'USER_DEACTIVATED', 'LOGIN',
                'FAB_CREATED', 'FAB_UPDATED', 'FAB_DELETED', 'BAA_CREATED', 'BAA_UPDATED', 'BAA_DELETED',
                'AREA_CREATED', 'AREA_UPDATED',
                'POP_CREATED', 'POP_UPDATED',
                'OLT_CREATED', 'OLT_UPDATED',
                'ODP_CREATED', 'ODP_UPDATED',
                'ONT_CREATED', 'ONT_UPDATED',
                'PAKET_CREATED', 'PAKET_UPDATED',
                'MATERIAL_CREATED', 'MATERIAL_UPDATED', 'MATERIAL_DELETED', 'MATERIAL_STOCK_ADDED',
                'SETTINGS_UPDATED',
                'KPI_EMPLOYEE_CREATED', 'KPI_EMPLOYEE_UPDATED', 'KPI_EMPLOYEE_PASSWORD_RESET', 'KPI_EMPLOYEE_STATUS_CHANGED',
                'KPI_SOFT_SKILL_CREATED', 'KPI_SOFT_SKILL_UPDATED', 'KPI_SOFT_SKILL_DELETED',
                'KPI_HARD_SKILL_CREATED', 'KPI_HARD_SKILL_UPDATED', 'KPI_HARD_SKILL_DELETED',
                'KPI_PERIOD_CREATED', 'KPI_PERIOD_UPDATED', 'KPI_PERIOD_DELETED', 'KPI_PERIOD_ACTIVATED', 'KPI_ASSESSMENTS_GENERATED',
                'KPI_SELF_ASSESSMENT_DONE', 'KPI_ATASAN_ASSESSMENT_DONE'
            )");
        }
    }
};
