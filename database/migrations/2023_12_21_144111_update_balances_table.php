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
        Schema::table('balances', function (Blueprint $table) {
            $table->dropColumn('balance_document_id');
            
            $table->dropIndex('balances_item_id_rental_id_index');
            $table->unique(['item_id', 'rental_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balances', function (Blueprint $table) {
            $table->integer('balance_document_id')->index();
            
            $table->dropIndex('balances_item_id_rental_id_unique');
            $table->index(['item_id', 'rental_id']);
        });
    }
};
