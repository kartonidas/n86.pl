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
        Schema::create('numbering', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64);
            $table->enum('type', ['rental']);
            $table->integer('number');
            $table->string('full_number', 50);
            $table->string('date', 7)->nullable()->default(null);
            $table->integer('object_id')->nullable()->default(null);
            
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('numbering');
    }
};
