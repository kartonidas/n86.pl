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
        Schema::create('item_bills', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64)->index();
            $table->integer('item_id');
            $table->integer('bill_type_id')->index();
            $table->date('payment_date')->nullable()->default(null);
            $table->tinyInteger('paid')->default(0);
            $table->date('paid_date')->nullable()->default(null);
            $table->decimal('cost', 8, 2);
            $table->timestamps();
            
            $table->index(['uuid', 'item_id'], 'index_uuid_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_bills');
    }
};
