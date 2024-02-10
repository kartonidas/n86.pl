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
            $table->enum('type', ['unpaid_bills', 'rental_ending', 'rental_ended', 'rental_coming'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('config_notifications', function (Blueprint $table) {
            $table->enum('type', ['unpaid_bills', 'rental_ending', 'rental_ended'])->change();
        });
    }
};
