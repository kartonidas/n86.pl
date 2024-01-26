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
        Schema::table('customer_invoice_items', function (Blueprint $table) {
            $table->decimal('vat_amount', 12, 4)->after('total_gross_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_invoice_items', function (Blueprint $table) {
            $table->dropColumn('vat_amount');
        });
    }
};
