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
        Schema::table('config_notifications', function (Blueprint $table) {
            $table->date('last_check')->nullable()->default(null)->after('mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('config_notifications', function (Blueprint $table) {
            $table->dropColumn('last_check');
        });
    }
};
