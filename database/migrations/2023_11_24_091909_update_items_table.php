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
        Schema::table('items', function (Blueprint $table) {
            $table->string('type', 80)->default('apartment')->change();
            $table->string('house_no', 20)->nullable()->change();
            
            $table->decimal('area', 8, 2)->default(0)->nullable()->after('zip');
            $table->tinyInteger('ownership')->default(1)->after('area');
            $table->tinyInteger('room_rental')->default(0)->after('ownership');
            $table->integer('num_rooms')->default(1)->nullable()->after('room_rental');
            $table->text('description')->nullable()->after('num_rooms');
            $table->decimal('default_rent', 8, 2)->nullable()->default(0)->after('description');
            $table->decimal('default_deposit', 8, 2)->nullable()->default(0)->after('default_rent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('area');
            
            $table->enum('type', ['estate'])->default('estate')->change();
            $table->string('house_no', 20)->change();
            
            $table->dropColumn('ownership');
            $table->dropColumn('room_rental');
            $table->dropColumn('num_rooms');
            $table->dropColumn('description');
            $table->dropColumn('default_rent');
            $table->dropColumn('default_deposit');
        });
    }
};
