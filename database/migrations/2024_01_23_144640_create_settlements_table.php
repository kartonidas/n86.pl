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
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64);
            $table->enum('document', ['cash_register', 'invoice']);
            $table->integer('document_id');
            $table->enum('object', ['invoice'])->nullalbe()->default(null);
            $table->integer('object_id')->nullalbe()->default(null);
            $table->decimal('amount', 12, 4)->nullalbe()->default(0);
            $table->date('payment_date');
            $table->integer('correction_id')->nullalbe()->default(null);
            $table->timestamps();
            
            $table->index(["uuid", "document"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
