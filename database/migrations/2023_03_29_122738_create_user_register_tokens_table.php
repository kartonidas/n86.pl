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
        Schema::create('user_register_tokens', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('token', 60)->unique();
            $table->string('code', 6)->unique();
            $table->timestamps();
            $table->dateTime('code_expired_at');
            
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_register_tokens');
    }
};
