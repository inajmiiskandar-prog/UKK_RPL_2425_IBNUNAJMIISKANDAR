<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material', function (Blueprint $table) {
            $table->id('id_material');
            $table->string('kode_material')->unique();
            $table->string('nama_material');
            $table->integer('stok')->default(0);
            $table->integer('minimal_stok')->default(5);
            $table->string('satuan');
            $table->decimal('harga', 12, 2);
            $table->enum('kondisi', ['BAIK', 'RUSAK'])->default('BAIK');
            $table->string('keterangan')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material');
    }
};
