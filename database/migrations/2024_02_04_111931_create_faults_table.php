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
        Schema::create('faults', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64);
            $table->integer('status_id');
            $table->integer('item_id');
            $table->text('description');
            $table->integer('created_user_id');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(["uuid"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faults');
    }
};
