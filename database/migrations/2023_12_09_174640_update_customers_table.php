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
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('total_items')->nullable()->default(0)->after('send_email');
            $table->integer('total_active_rentals')->nullable()->default(0)->after('total_items');
            $table->integer('total_waiting_rentals')->nullable()->default(0)->after('total_active_rentals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('total_items');
            $table->dropColumn('total_active_rentals');
            $table->dropColumn('total_waiting_rentals');
        });
    }
};
