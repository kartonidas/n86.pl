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
            $table->tinyInteger('paid')->default(0)->after('document_ids');
            $table->integer('paid_date')->nullable()->default(null)->after('paid');
            $table->string('payment_method', 50)->nullable()->default(null)->after('paid_date');
            $table->integer('source_paid_document')->nullable()->default(null)->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balance_documents', function (Blueprint $table) {
            $table->dropColumn('paid');
            $table->dropColumn('paid_date');
            $table->dropColumn('payment_method');
            $table->dropColumn('source_paid_document');
        });
    }
};
