<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olt', function (Blueprint $table) {
            $table->id('id_olt');
            $table->string('kode_olt')->unique();
            $table->string('nama_olt');
            $table->string('lokasi');
            $table->decimal('latitude', 65, 30);
            $table->decimal('longitude', 65, 30);
            $table->foreignId('id_pop')->constrained('pop', 'id_pop');
            $table->string('ip_olt')->nullable();
            $table->string('username_olt')->nullable();
            $table->string('password_olt')->nullable();
            $table->string('foto_olt')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olt');
    }
};
