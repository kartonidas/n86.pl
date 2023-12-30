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
        Schema::table('item_cyclical_fees', function (Blueprint $table) {
            $table->integer('last')->nullable()->default(null)->after('beginning');
            $table->dropColumn('last_generated');
            $table->dropColumn('beginning');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_cyclical_fees', function (Blueprint $table) {
            $table->dropColumn('last');
            $table->integer('beginning')->after('bill_type_id');
            $table->integer('last_generated')->nullable()->default(null)->after('beginning');
        });
    }
};
