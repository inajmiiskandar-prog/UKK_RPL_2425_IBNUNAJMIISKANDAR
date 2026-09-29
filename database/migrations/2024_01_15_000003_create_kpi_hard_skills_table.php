<?php

/**
 * Migration untuk membuat tabel kpi_hard_skills
 * Menyimpan daftar indikator penilaian hard skill
 * Contoh: Penguasaan Teknis, Problem Solving, dll.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_hard_skills', function (Blueprint $table) {
            $table->id();
            $table->string('nama_indikator', 200);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_hard_skills');
    }
};
