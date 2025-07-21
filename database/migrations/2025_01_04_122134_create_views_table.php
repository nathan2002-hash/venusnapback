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
        Schema::create('views', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('ip_address');
            $table->string('duration');
            $table->string('post_media_id');
            $table->longText('user_agent');
            $table->longText('device_info')->nullable();
            $table->string('history_status')->nullable();
            $table->string('clicked')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('views');
    }
};
