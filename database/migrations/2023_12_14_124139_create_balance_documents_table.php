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
        Schema::create('balance_documents', function (Blueprint $table) {
            $table->id();
            
            $table->integer('time');
            $table->string('object_type', 50);
            $table->integer('object_id');
            $table->decimal('amount', 8, 2);
            $table->char('operation_type', 1);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_documents');
    }
};
