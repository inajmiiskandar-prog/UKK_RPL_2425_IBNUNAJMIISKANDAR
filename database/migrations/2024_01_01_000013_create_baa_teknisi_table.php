<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('baa_teknisi', function (Blueprint $table) {
            $table->id('id_baa_teknisi');
            $table->foreignId('id_baa')->constrained('baa', 'id_baa');
            $table->foreignId('id_user')->constrained('users', 'id_user');
            $table->timestamp('createdAt')->useCurrent();

            // Satu teknisi tidak boleh dobel-input di BAA yang sama
            $table->unique(['id_baa', 'id_user']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('baa_teknisi');
    }
};
