<?php

/**
 * Migration untuk membuat tabel kpi_soft_skills
 * Menyimpan daftar indikator penilaian soft skill
 * Contoh: Komunikasi, Kerja Tim, Disiplin, dll.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_soft_skills', function (Blueprint $table) {
            $table->id();
            $table->string('nama_indikator', 200);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_soft_skills');
    }
};
