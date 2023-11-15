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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64)->index();
            $table->enum('type', ['estate'])->default('estate');
            $table->tinyInteger('active')->default(1);
            $table->string('name', 100);
            $table->string('street', 80);
            $table->string('house_no', 20);
            $table->string('apartment_no', 20)->nullable();
            $table->string('city', 120);
            $table->string('zip', 10);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
