<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id('id_log');
            $table->enum('type', [
                // User
                'USER_CREATED', 'USER_UPDATED', 'USER_DELETED', 'USER_DEACTIVATED', 'LOGIN',
                // Transaksi
                'FAB_CREATED', 'FAB_UPDATED', 'BAA_CREATED', 'BAA_UPDATED',
                // Master Data
                'AREA_CREATED', 'AREA_UPDATED',
                'POP_CREATED', 'POP_UPDATED',
                'OLT_CREATED', 'OLT_UPDATED',
                'ODP_CREATED', 'ODP_UPDATED',
                'ONT_CREATED', 'ONT_UPDATED',
                'PAKET_CREATED', 'PAKET_UPDATED',
                'MATERIAL_CREATED', 'MATERIAL_UPDATED',
                // Pengaturan
                'SETTINGS_UPDATED',
            ]);
            $table->text('description');
            $table->foreignId('id_user')->nullable()->constrained('users', 'id_user');
            $table->timestamp('createdAt')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
