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
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->enum('customer_type', ['firm', 'person'])->default('person')->after('customer_id');
            $table->string('customer_name', 80)->after('customer_type');
            $table->string('customer_street', 80)->after('customer_name');
            $table->string('customer_house_no', 20)->nullable()->after('customer_street');
            $table->string('customer_apartment_no', 20)->nullable()->after('customer_house_no');
            $table->string('customer_city', 120)->after('customer_apartment_no');
            $table->string('customer_zip', 10)->after('customer_city');
            $table->char('customer_country', 2)->default('pl')->after('customer_zip');
            $table->string('customer_nip', 20)->nullable()->after('customer_country');
            
            $table->integer('customer_id')->nullable()->change();
            
            $table->dropColumn('recipient_id');
            $table->dropColumn('payer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropColumn('customer_type');
            $table->dropColumn('customer_name');
            $table->dropColumn('customer_street');
            $table->dropColumn('customer_house_no');
            $table->dropColumn('customer_apartment_no');
            $table->dropColumn('customer_city');
            $table->dropColumn('customer_zip');
            $table->dropColumn('customer_country');
            $table->dropColumn('customer_nip');
            
            $table->integer('customer_id')->nullable(false)->change();
            
            $table->integer('recipient_id')->nullable()->after('customer_id');
            $table->integer('payer_id')->nullable()->after('recipient_id');
        });
    }
};
