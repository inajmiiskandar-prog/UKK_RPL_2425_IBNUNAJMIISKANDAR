<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Kolom baru ditambahkan secara additive - kolom lama (nama_indikator, deskripsi)
     * tetap ada untuk menjaga kompatibilitas data yang sudah ada.
     */
    public function up(): void
    {
        Schema::table('kpi_hard_skills', function (Blueprint $table) {
            // Kolom baru untuk struktur hard skill per divisi/jabatan
            $table->string('divisi', 100)->nullable()->after('deskripsi');
            $table->string('jabatan', 100)->nullable()->after('divisi');
            $table->text('responsibilities')->nullable()->after('jabatan');
            $table->string('kpi', 255)->nullable()->after('responsibilities');
            $table->string('target', 50)->nullable()->default('100%')->after('kpi');
            $table->decimal('weight', 5, 2)->nullable()->default(0)->after('target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_hard_skills', function (Blueprint $table) {
            $table->dropColumn(['divisi', 'jabatan', 'responsibilities', 'kpi', 'target', 'weight']);
        });
    }
};
