<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('kode_user')->unique();
            $table->string('nama');
            $table->string('username')->nullable()->unique();
            $table->string('password');
            $table->enum('jkl', ['LAKI_LAKI', 'PEREMPUAN'])->nullable();
            $table->string('foto')->nullable();
            $table->enum('role', ['ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK'])->default('SALES');
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->boolean('status')->default(true);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
