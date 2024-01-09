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
            $table->dropColumn('nip');
            $table->dropColumn('street');
            $table->dropColumn('house_no');
            $table->dropColumn('apartment_no');
            $table->dropColumn('city');
            $table->dropColumn('zip');
            $table->dropColumn('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            $table->string("nip", 20)->nullable()->after('email');
            $table->string('street', 80)->nullable()->after('name');
            $table->string('house_no', 20)->nullable()->after('street');
            $table->string('apartment_no', 20)->nullable()->after('house_no');
            $table->string('city', 120)->nullable()->after('apartment_no');
            $table->string('zip', 10)->nullable()->after('city');
            $table->char('country', 2)->nullable()->after('zip');
        });
    }
};
