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
        Schema::create('item_cyclical_fees', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64);
            $table->integer('item_id');
            $table->integer('bill_type_id');
            $table->integer('beginning');
            $table->integer('payment_day');
            $table->integer('repeat_months');
            $table->decimal('cost', 8, 2);
            $table->string('recipient_name', 250)->nullable()->default(null);
            $table->text('recipient_desciption')->nullable()->default(null);
            $table->string('recipient_bank_account', 50)->nullable()->default(null);
            $table->string('source_document_number', 100)->nullable()->default(null);
            $table->integer('source_document_date')->nullable()->default(null);
            $table->tinyInteger('tenant_cost')->default(1);
            $table->text('comments')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['uuid', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_cyclical_fees');
    }
};
