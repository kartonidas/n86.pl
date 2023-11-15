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
        Schema::create('item_fees', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id')->index();
            $table->integer('fee_type_id')->index();
            $table->date('beginning');
            $table->date('end')->nullable()->default(null);
            $table->decimal('cost', 8, 2);
            $table->timestamps();
            
            $table->index(['item_id', 'fee_type_id'], 'index_item_id_fee_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_fees');
    }
};
