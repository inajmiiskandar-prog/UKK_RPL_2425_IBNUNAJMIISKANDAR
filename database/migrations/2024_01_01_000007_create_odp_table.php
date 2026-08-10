<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odp', function (Blueprint $table) {
            $table->id('id_odp');
            $table->string('kode_odp')->unique();
            $table->string('nama_odp');
            $table->string('alamat');
            $table->decimal('latitude', 65, 30);
            $table->decimal('longitude', 65, 30);
            $table->integer('jumlah_port')->nullable();
            $table->integer('stok_port')->default(0)->nullable();
            $table->foreignId('id_olt')->constrained('olt', 'id_olt');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odp');
    }
};
