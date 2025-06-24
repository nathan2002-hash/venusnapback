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
        Schema::create('blocked_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ip')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('url');
            $table->string('user_agent')->nullable();
            $table->integer('status_code');
            $table->integer('attempts')->default(1);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_requests');
    }
};
