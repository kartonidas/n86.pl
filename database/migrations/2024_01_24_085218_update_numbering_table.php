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
        Schema::table('numbering', function (Blueprint $table) {
            $table->enum('type', ['rental', 'customer_invoice'])->change();
            $table->integer('sale_register_id')->nullable()->defalut(null);
            
            $table->index(["type", "uuid"]);
            $table->index(["type", "object_id"]);
            $table->index(["type", "sale_register_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('numbering', function (Blueprint $table) {
            $table->enum('type', ['rental'])->change();
            $table->dropColumn('sale_register_id');
            
            $table->dropIndex("numbering_type_uuid_index");
            $table->dropIndex("numbering_type_object_id_index");
            $table->dropIndex("numbering_type_sale_register_id_index");
        });
    }
};
