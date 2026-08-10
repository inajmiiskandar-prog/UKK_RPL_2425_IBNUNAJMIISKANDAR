<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pop', function (Blueprint $table) {
            $table->id('id_pop');
            $table->string('kode_pop')->unique();
            $table->string('nama_pop');
            $table->string('alamat');
            $table->decimal('latitude', 65, 30);
            $table->decimal('longitude', 65, 30);
            $table->foreignId('id_area')->constrained('area', 'id_area');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pop');
    }
};
