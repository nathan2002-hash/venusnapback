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
        Schema::create('point_requests', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('full_name');
            $table->string('business_name')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->integer('points');
            $table->text('purpose');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_requests');
    }
};
