<?php

/**
 * Migration untuk menambahkan kolom-kolom KPI ke tabel users
 * - nik: NIK karyawan
 * - divisi: Divisi karyawan
 * - jabatan: Jabatan karyawan
 * - atasan_id: Foreign key ke users.id_user (atasan langsung)
 *
 * Tambahan juga menambahkan role "ATASAN" ke enum role yang sudah ada
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kolom-kolom baru untuk data karyawan (nullable semua agar data lama tetap aman)
            $table->string('nik', 20)->nullable()->after('email');
            $table->string('divisi', 100)->nullable()->after('nik');
            $table->string('jabatan', 100)->nullable()->after('divisi');
            $table->unsignedBigInteger('atasan_id')->nullable()->after('jabatan');

            // Tambahkan foreign key constraint untuk atasan_id
            // menggunakan event('eloquent.created: App\Models\User') untuk auto-fill saat insert
            $table->foreign('atasan_id')
                  ->references('id_user')
                  ->on('users')
                  ->onDelete('set null');
        });

        // Update enum role untuk menambahkan ATASAN (MySQL syntax)
        // Catatan: Laravel tidak punya cara langsung untuk alter enum, jadi kita pakai DB statement
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK', 'ATASAN') DEFAULT 'SALES'");
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key dulu
            $table->dropForeign(['atasan_id']);
            // Hapus kolom
            $table->dropColumn(['nik', 'divisi', 'jabatan', 'atasan_id']);
        });

        // Kembalikan enum ke aslinya
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK') DEFAULT 'SALES'");
        }
    }
};
