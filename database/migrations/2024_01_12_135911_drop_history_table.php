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
        Schema::dropIfExists('history');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('history', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64);
            $table->string('event', 50);
            $table->string('object_type', 50);
            $table->integer('object_id');
            $table->string('parent_object_type', 50)->nullable();
            $table->integer('parent_object_id')->nullable();
            $table->integer('user_id');
            $table->text('log')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['uuid', 'object_type', 'object_id']);
        });
    }
};
