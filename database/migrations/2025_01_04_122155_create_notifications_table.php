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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('action');
            $table->string('notifiable_id');
            $table->longText('notifiable_type');
            $table->json('data');
            $table->boolean('is_read')->default(false);
            $table->integer('group_count')->default(0); // Tracks the number of users involved in the grouped notification
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
