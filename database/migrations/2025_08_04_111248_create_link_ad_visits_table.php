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
        Schema::create('link_ad_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_ad_share_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_id')->nullable();
            $table->boolean('is_logged_in')->default(false);
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('device_info')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_ad_visits');
    }
};
