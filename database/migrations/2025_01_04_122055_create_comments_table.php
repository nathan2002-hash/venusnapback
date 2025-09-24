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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('post_media_id');
            $table->string('comment')->nullable();
            $table->enum('type', ['text', 'gif'])->default('text'); // Add type column
            $table->string('gif_provider')->nullable(); // 'giphy' or 'tenor'
            $table->string('gif_id')->nullable(); // GIF ID from provider
            $table->string('gif_url')->nullable(); // GIF URL from provider
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
