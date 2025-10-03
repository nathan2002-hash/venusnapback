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
        Schema::create('ai_chat_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Memory types
            $table->enum('memory_type', [
                'user_profile',      // User details
                'album_context',     // Album information
                'conversation_summary', // Chat summary
                'key_facts',         // Important facts
                'user_preferences',  // User likes/dislikes
                'project_context',   // Project-specific context
                'function_results'   // Cached function results
            ]);

            $table->text('content'); // The memory content
            $table->json('metadata')->nullable(); // Structured data
            $table->integer('importance')->default(1); // 1-10 scale
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // For temporary memories
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chat_memories');
    }
};
