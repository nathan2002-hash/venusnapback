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
        Schema::create('monetization_requests', function (Blueprint $table) {
            $table->id();
            $table->string('album_id');
            $table->string('country');
            $table->string('user_id');
            $table->string('status')->default('pending');
            $table->text('device_info')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monetization_requests');
    }
};
