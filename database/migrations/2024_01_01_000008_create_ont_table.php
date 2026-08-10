<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ont', function (Blueprint $table) {
            $table->id('id_ont');
            $table->string('serial_number')->unique();
            $table->string('pelanggan');
            $table->enum('status', ['TERSEDIA', 'TERPASANG', 'RUSAK'])->default('TERSEDIA');
            $table->foreignId('id_pop')->constrained('pop', 'id_pop');
            $table->foreignId('id_odp')->constrained('odp', 'id_odp');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ont');
    }
};
