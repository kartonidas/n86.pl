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
        Schema::table('rentals', function (Blueprint $table) {
            $table->integer('tenant_id')->after('item_id');
            $table->enum('period', ['month', 'indeterminate', 'date'])->default('month')->after('start');
            $table->integer('months')->nullable()->default(null)->after('period');
            $table->enum('termination_period', ['months', 'days'])->default('months')->after('end');
            $table->integer('termination_months')->nullable()->default(null)->after('termination_period');
            $table->integer('termination_days')->nullable()->default(null)->after('termination_months');
            $table->decimal('deposit', 8, 2)->nullable()->default(null)->after('termination_days');
            $table->enum('payment', ['cyclical', 'onetime'])->default('cyclical')->after('deposit');
            $table->decimal('rent', 8, 2)->after('payment');
            $table->decimal('first_month_different_amount', 8, 2)->nullable()->default(null)->after('rent');
            $table->decimal('last_month_different_amount', 8, 2)->nullable()->default(null)->after('first_month_different_amount');
            $table->integer('payment_day')->nullable()->default(null)->after('last_month_different_amount');
            $table->integer('first_payment_date')->after('payment_day');
            $table->integer('number_of_people')->after('first_payment_date');
            $table->text('comments')->nullable()->default(null)->after('number_of_people');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
            $table->dropColumn('period');
            $table->dropColumn('months');
            $table->dropColumn('termination_period');
            $table->dropColumn('termination_months');
            $table->dropColumn('termination_days');
            $table->dropColumn('deposit');
            $table->dropColumn('payment');
            $table->dropColumn('rent');
            $table->dropColumn('first_month_different_amount');
            $table->dropColumn('last_month_different_amount');
            $table->dropColumn('payment_day');
            $table->dropColumn('first_payment_date');
            $table->dropColumn('number_of_people');
            $table->dropColumn('comments');
        });
    }
};
