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
            $table->enum('type', ['customer', 'tenant', 'firm', 'person'])->default('person')->change();
            $table->enum('role', ['customer', 'tenant'])->default('customer')->after('uuid');
        });
        
        DB::table('customers')->update(['type' => 'person']);
        
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('type', ['firm', 'person'])->default('person')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->dropColumn('type');
            
        });
        
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('type', ['customer', 'tenant'])->default('customer')->after('uuid');
        });
    }
};
