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
            $table->integer('termination_time')->nullable()->default(null)->after('termination');
            $table->integer('termination_added')->nullable()->default(null)->after('termination_time');
            $table->enum('status', ['archive', 'current', 'termination', 'waiting'])->default('waiting')->change();
            $table->integer('number')->after('tenant_id')->nullable()->default(null);
            $table->string('full_number', 50)->after('number')->nullable()->default(null);
            $table->date('document_date')->after('tenant_id')->nullable()->default(null);
            $table->text('termination_reason')->after('termination_added')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn('termination_time');
            $table->dropColumn('termination_added');
            $table->enum('status', ['archive', 'current', 'waiting'])->default('waiting')->change();
            $table->dropColumn('number');
            $table->dropColumn('full_number');
            $table->dropColumn('document_date');
            $table->dropColumn('termination_reason');
        });
    }
};
