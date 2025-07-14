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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['queued', 'active', 'seen', 'expired'])->default('queued');
            $table->decimal('score', 8, 2)->index();
            $table->unsignedInteger('sequence_order')->index();
            $table->string('source')->nullable()->index(); // 'engagement', 'preference', 'fresh', 'fallback'
            $table->timestamp('seen_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
            $table->unique(['user_id', 'post_id']);
            $table->index(['user_id', 'status', 'sequence_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
