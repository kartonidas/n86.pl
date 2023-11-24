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
            $table->string('street', 80)->nullable()->change();
            $table->string('house_no', 20)->nullable()->change();
            $table->string('city', 120)->nullable()->change();
            $table->string('zip', 10)->nullable()->change();
            $table->string('nip', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
        });
    }
};
