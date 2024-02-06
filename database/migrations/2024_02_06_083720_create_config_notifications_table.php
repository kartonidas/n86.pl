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
        Schema::create('config_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64);
            $table->enum('owner', ['user', 'tenant', 'customer']);
            $table->integer('owner_id');
            $table->enum('type', ['unpaid_bills', 'rental_ending', 'rental_ended']);
            $table->integer('days')->nullable()->default(null);
            $table->enum('mode', ['single', 'group', 'group_object']);
            $table->timestamps();
            
            $table->index(["uuid"]);
            $table->index(["uuid", "type"]);
            $table->index(["uuid", "owner", "owner_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_notifications');
    }
};
