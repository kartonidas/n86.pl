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
        Schema::table('item_bills', function (Blueprint $table) {
            $table->integer('rental_id')->nullable()->default(null)->after('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_bills', function (Blueprint $table) {
            $table->dropColumn('rental_id');
        });
    }
};
