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
        Schema::create('customer_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64);
            $table->enum('type', ['invoice', 'proforma', 'correction']);
            $table->integer('number')->nullable()->default(null);
            $table->integer('proforma_id')->nullable()->default(null);
            $table->integer('correction_id')->nullable()->default(null);
            $table->string('full_number', 100)->nullable()->default(null);
            $table->integer('sale_register_id');
            $table->integer('created_user_id');
            $table->integer('customer_id');
            $table->integer('recipient_id')->nullable()->default(null);
            $table->integer('payer_id')->nullable()->default(null);
            $table->text('comment')->nullable()->default(null);
            $table->date('document_date');
            $table->date('sell_date');
            $table->date('payment_date');
            $table->integer('payment_type_id');
            $table->string('account_number', 60)->nullable()->default(null);
            $table->string('swift_number', 60)->nullable()->default(null);
            $table->decimal('net_amount', 12, 4)->nullable()->default(0);
            $table->decimal('gross_amount', 12, 4)->nullable()->default(0);
            $table->decimal('net_amount_discount', 12, 4)->nullable()->default(0);
            $table->decimal('gross_amount_discount', 12, 4)->nullable()->default(0);
            $table->string('language', 2)->default('pl');
            $table->string('currency', 10)->default('PLN');
            $table->decimal('total_payments', 12, 4)->nullable()->default(0);
            $table->decimal('balance', 12, 4)->nullable()->default(0);
            $table->decimal('balance_correction', 12, 4)->nullable()->default(0);
            $table->enum('source', ['direct'])->nullable()->default('direct');
            $table->timestamps();
            
            $table->index(["uuid", "type"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_invoices');
    }
};
