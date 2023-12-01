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
        Schema::table('customers', function (Blueprint $table) {
            $table->text('comments')->nullable()->default(null)->after('document_number');
            $table->tinyInteger('send_sms')->default(1)->after('comments');
            $table->tinyInteger('send_email')->default(1)->after('send_sms');
            $table->char('country', 2)->after('zip')->nullable()->default(null);
            
            $table->dropIndex('customers_uuid_index');
            $table->index(['uuid', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('comments');
            $table->dropColumn('send_sms');
            $table->dropColumn('send_email');
            $table->dropColumn('country');
            
            $table->dropIndex('customers_uuid_role_index');
            $table->index('uuid');
        });
    }
};
