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
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64);
            $table->integer('item_id');
            $table->integer('rental_id');
            $table->decimal('amount', 8, 2);
            $table->integer('balance_document_id')->index();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['item_id', 'rental_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
