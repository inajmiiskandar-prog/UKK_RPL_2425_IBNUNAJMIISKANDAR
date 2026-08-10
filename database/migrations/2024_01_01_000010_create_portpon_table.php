<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portpon', function (Blueprint $table) {
            $table->id('id_port');
            $table->integer('nomor_port');
            $table->string('tipe_kartu');
            $table->enum('status', ['TERSEDIA', 'TERPASANG', 'RUSAK'])->default('TERSEDIA');
            $table->foreignId('id_olt')->constrained('olt', 'id_olt');
            $table->foreignId('id_odp')->nullable()->constrained('odp', 'id_odp');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['id_olt', 'nomor_port']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portpon');
    }
};
