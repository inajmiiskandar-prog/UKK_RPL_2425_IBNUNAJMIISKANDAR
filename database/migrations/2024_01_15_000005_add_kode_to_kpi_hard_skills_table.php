<?php

/**
 * Migration untuk menambahkan kolom kode ke tabel kpi_hard_skills
 * Format kode: HS001, HS002, dst
 * Dengan logika isi celah (gap filling)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_hard_skills', function (Blueprint $table) {
            $table->string('kode', 10)->unique()->after('id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('kpi_hard_skills', function (Blueprint $table) {
            $table->dropColumn('kode');
        });
    }
};
