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
        Schema::create('media_downloads', function (Blueprint $table) {
            $table->id();
            $table->string('post_media_id')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_info')->nullable();
            $table->string('user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_downloads');
    }
};
