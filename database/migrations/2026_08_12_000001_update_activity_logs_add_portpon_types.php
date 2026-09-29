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
            'PORTPON_CREATED', 'PORTPON_DELETED',
            'SETTINGS_UPDATED'
        )");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

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
            'SETTINGS_UPDATED'
        )");
    }
};
