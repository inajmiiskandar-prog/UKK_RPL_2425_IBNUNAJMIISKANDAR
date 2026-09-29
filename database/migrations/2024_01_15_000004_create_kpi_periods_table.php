<?php

/**
 * Migration untuk membuat tabel kpi_periods
 * Menyimpan periode penilaian KPI (misal: Januari 2024, Q1 2024, dll.)
 * Status: draft (belum aktif), aktif (sedang berjalan), selesai (sudah ditutup)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_periods', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100); // Contoh: "Januari 2024", "Q1 2024"
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_periods');
    }
};
