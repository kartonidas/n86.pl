<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('history', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64);
            $table->integer('history_log_id');
            $table->string('event', 50);
            $table->string('object_type', 50);
            $table->integer('object_id');
            $table->integer('user_id');
            $table->timestamps();
            
            $table->index(['uuid', 'object_type', 'object_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history');
    }
};
