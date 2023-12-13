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
        Schema::create('item_cyclical_fee_costs', function (Blueprint $table) {
            $table->id();
            $table->integer('item_cyclical_fee_id');
            $table->integer('from_time');
            $table->decimal('cost', 8, 2);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('item_cyclical_fee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_cyclical_fee_costs');
    }
};
