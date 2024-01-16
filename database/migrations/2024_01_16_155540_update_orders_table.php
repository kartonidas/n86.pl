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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('unit_price', 12, 4)->change();
            $table->decimal('unit_price_gross', 12, 4)->change();
            $table->decimal('amount', 12, 4)->change();
            $table->decimal('gross', 12, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('unit_price', 8, 2)->change();
            $table->decimal('unit_price_gross', 8, 2)->change();
            $table->decimal('amount', 8, 2)->change();
            $table->decimal('gross', 8, 2)->change();
        });
    }
};
