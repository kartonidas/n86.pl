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
        Schema::table('balance_documents', function (Blueprint $table) {
            $table->dropColumn('refer');
            $table->dropColumn('refer_id');
            $table->integer('item_id')->after('id');
            $table->integer('balance_id')->after('amount');
            
            $table->dropIndex('balance_documents_refer_refer_id_object_type_index');
            $table->index(['item_id', 'balance_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balance_documents', function (Blueprint $table) {
            $table->enum('refer', ['item'])->default('item')->after('id');
            $table->integer('refer_id')->after('refer');
            
            $table->dropColumn('item_id');
            $table->dropColumn('balance_id');
            
            $table->index(['refer', 'refer_id', 'object_type']);
        });
    }
};
