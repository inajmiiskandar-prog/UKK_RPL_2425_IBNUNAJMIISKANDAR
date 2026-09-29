<?php

/**
 * Migration untuk menambahkan kolom alamat dan tanggal_masuk ke tabel users
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('alamat', 255)->nullable()->after('no_hp');
            $table->date('tanggal_masuk')->nullable()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'tanggal_masuk']);
        });
    }
};
