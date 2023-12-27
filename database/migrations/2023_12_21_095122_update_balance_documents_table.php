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
            $table->enum('refer', ['item'])->default('item')->after('id');
            $table->integer('refer_id')->after('refer');
            $table->string('document_ids', 250)->nullable()->default(null)->after('operation_type');
            $table->dropColumn('operation_type');
            
            $table->index(['refer', 'refer_id', 'object_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balance_documents', function (Blueprint $table) {
            $table->dropColumn('refer');
            $table->dropColumn('refer_id');
            $table->dropColumn('document_ids');
            $table->char('operation_type', 1)->after('amount');
            
            $table->dropIndex('balance_documents_refer_refer_id_object_type_index');
        });
    }
};
