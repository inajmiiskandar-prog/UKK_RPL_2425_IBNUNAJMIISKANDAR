<?php

/**
 * Migration untuk membuat tabel kpi_assessments
 * Menyimpan data penilaian KPI per karyawan per periode
 * Status: pending (belum dinilai), self_done (self assessment selesai),
 *         atasan_done (penilaian atasan selesai), selesai (keduanya selesai)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Karyawan yang dinilai
            $table->unsignedBigInteger('kpi_period_id'); // Periode KPI
            $table->unsignedBigInteger('atasan_id')->nullable(); // Atasan yang menilai (bisa di-override manual)
            $table->enum('status', ['pending', 'self_done', 'atasan_done', 'selesai', 'menunggu_review', 'sudah_dicek'])->default('pending');
            $table->decimal('skor_akhir', 5, 2)->nullable(); // Rata-rata skor dari self + atasan
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('kpi_period_id')->references('id')->on('kpi_periods')->onDelete('cascade');
            $table->foreign('atasan_id')->references('id_user')->on('users')->onDelete('set null');

            // Unique constraint: 1 karyawan per periode
            $table->unique(['user_id', 'kpi_period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_assessments');
    }
};
