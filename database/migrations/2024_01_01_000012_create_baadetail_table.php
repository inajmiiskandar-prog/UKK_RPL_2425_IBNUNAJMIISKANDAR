<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('baadetail', function (Blueprint $table) {
            $table->id('id_baa_detail');
            $table->foreignId('id_baa')->constrained('baa', 'id_baa');
            $table->foreignId('id_material')->constrained('material', 'id_material');
            $table->integer('jumlah');
            $table->string('keterangan')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('baadetail');
    }
};
