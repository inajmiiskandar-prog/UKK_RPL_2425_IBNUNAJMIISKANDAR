<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket', function (Blueprint $table) {
            $table->id('id_paket');
            $table->string('kode_paket')->unique();
            $table->string('nama_paket');
            $table->string('kecepatan');
            $table->decimal('harga', 12, 2);
            $table->string('keterangan')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket');
    }
};
