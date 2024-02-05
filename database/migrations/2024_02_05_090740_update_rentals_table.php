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
        Schema::table('rentals', function (Blueprint $table) {
            $table->enum('status', ['archive','current','termination','waiting', 'canceled'])->default('waiting')->change();
            $table->string('currency', 5)->default('PLN')->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->enum('status', ['archive','current','termination','waiting'])->default('waiting')->change();
            $table->dropColumn('currency');
        });
    }
};
