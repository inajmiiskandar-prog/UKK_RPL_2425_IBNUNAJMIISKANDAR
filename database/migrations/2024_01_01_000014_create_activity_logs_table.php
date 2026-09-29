<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id('id_log');
            $table->string('type', 255);
            $table->text('description');
            $table->foreignId('id_user')->nullable()->constrained('users', 'id_user');
            $table->timestamp('createdAt')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
