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
        Schema::table('item_bills', function (Blueprint $table) {
            $table->string('recipient_name', 250)->nullable()->default(null)->after('cost');
            $table->text('recipient_desciption', 250)->nullable()->default(null)->after('recipient_name');
            $table->string('recipient_bank_account', 50)->nullable()->default(null)->after('recipient_desciption');
            $table->text('comments')->nullable()->default(null)->after('recipient_bank_account');
            $table->softDeletes();
            
            $table->integer('payment_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_bills', function (Blueprint $table) {
            $table->dropColumn('recipient_name');
            $table->dropColumn('recipient_desciption');
            $table->dropColumn('recipient_bank_account');
            $table->dropColumn('comments');
            $table->dropColumn('deleted_at');
            
            $table->date('payment_date')->change();
        });
    }
};
