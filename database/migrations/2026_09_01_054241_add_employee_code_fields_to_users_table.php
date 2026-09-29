<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'kode_karyawan')) {
                $table->string('kode_karyawan')->nullable()->unique()->after('kode_user');
            }

            if (!Schema::hasColumn('users', 'no_telp')) {
                $table->string('no_telp')->nullable()->after('alamat');
            }

            if (!Schema::hasColumn('users', 'join_date')) {
                $table->date('join_date')->nullable()->after('no_telp');
            }

            if (!Schema::hasColumn('users', 'employee_status')) {
                $table->enum('employee_status', ['Active', 'Inactive'])->nullable()->default('Active')->after('join_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('users', 'kode_karyawan') ? 'kode_karyawan' : null,
                Schema::hasColumn('users', 'no_telp') ? 'no_telp' : null,
                Schema::hasColumn('users', 'join_date') ? 'join_date' : null,
                Schema::hasColumn('users', 'employee_status') ? 'employee_status' : null,
            ]));
        });
    }
};
