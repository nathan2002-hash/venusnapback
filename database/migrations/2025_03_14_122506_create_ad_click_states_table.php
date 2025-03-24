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
        Schema::create('ad_click_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id');
            $table->foreignId('user_id');
            $table->foreignId('ad_session_id');
            $table->integer('time_spent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_click_states');
    }
};
