<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fab', function (Blueprint $table) {
            $table->id('id_fab');
            $table->string('kode_fab')->unique();
            $table->string('nama_pelanggan');
            $table->string('nik')->unique();
            $table->string('foto')->nullable();
            $table->string('no_hp');
            $table->string('alamat');
            $table->decimal('latitude', 65, 30);
            $table->decimal('longitude', 65, 30);
            $table->enum('status', ['OPEN', 'AKTIF'])->default('OPEN');
            $table->foreignId('id_area')->constrained('area', 'id_area');
            $table->foreignId('id_paket')->constrained('paket', 'id_paket');
            $table->foreignId('id_user')->constrained('users', 'id_user');
            $table->foreignId('id_penginput')->constrained('users', 'id_user');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fab');
    }
};
