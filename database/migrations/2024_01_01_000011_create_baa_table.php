<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('baa', function (Blueprint $table) {
            $table->id('id_baa');
            $table->string('kode_baa')->unique();
            $table->dateTime('tanggal_instalasi');
            $table->enum('status', ['SELESAI'])->default('SELESAI');
            $table->string('catatan')->nullable();
            $table->string('foto_instalasi')->nullable();
            $table->foreignId('id_fab')->constrained('fab', 'id_fab');
            $table->foreignId('id_user')->constrained('users', 'id_user');
            $table->foreignId('id_olt')->constrained('olt', 'id_olt');
            $table->foreignId('id_ont')->constrained('ont', 'id_ont');
            $table->foreignId('id_odp')->constrained('odp', 'id_odp');
            $table->decimal('ping_ms', 10, 2)->nullable();
            $table->integer('port_odp')->nullable();
            $table->integer('port_olt')->nullable();
            $table->decimal('rx_power_dbm', 10, 2)->nullable();
            $table->string('speed_download')->nullable();
            $table->string('speed_upload')->nullable();
            $table->decimal('tx_power_dbm', 10, 2)->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('baa');
    }
};
