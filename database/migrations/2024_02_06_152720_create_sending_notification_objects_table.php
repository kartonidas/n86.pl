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
        Schema::create('sending_notification_objects', function (Blueprint $table) {
            $table->id();
            $table->integer("config_notification_id");
            $table->integer("object_id");
            $table->date("date");
            $table->index(["config_notification_id", "date"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sending_notification_objects');
    }
};
