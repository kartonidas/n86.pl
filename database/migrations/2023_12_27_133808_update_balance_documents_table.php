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
            $table->text('comments')->nullable()->default(null)->after('source_paid_document');

            $table->index(['object_type', 'object_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balance_documents', function (Blueprint $table) {
            $table->dropColumn('comments');
            
            $table->dropIndex('balance_documents_object_type_object_id_index');
        });
    }
};
