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
        Schema::table('firms', function (Blueprint $table) {
            $table->tinyInteger('firebase')->nullable()->default(0)->after('phone');
            $table->string('firebase_password')->nullable()->default(null)->after('firebase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            $table->dropColumn('firebase');
            $table->dropColumn('firebase_password');
        });
    }
};
