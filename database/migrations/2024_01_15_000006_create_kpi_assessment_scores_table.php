<?php

/**
 * Migration untuk membuat tabel kpi_assessment_scores
 * Menyimpan skor per indikator per penilai
 * Skill_type: soft_skill atau hard_skill
 * Penilai_type: karyawan (self assessment) atau atasan (penilaian atasan)
 * Skor: integer 1-100
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kpi_assessment_id');
            $table->enum('skill_type', ['soft_skill', 'hard_skill']);
            $table->unsignedBigInteger('skill_id'); // ID dari kpi_soft_skills atau kpi_hard_skills
            $table->enum('penilai_type', ['karyawan', 'atasan']);
            $table->integer('skor'); // 1-100
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Foreign key ke kpi_assessments
            $table->foreign('kpi_assessment_id')
                  ->references('id')
                  ->on('kpi_assessments')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_assessment_scores');
    }
};
