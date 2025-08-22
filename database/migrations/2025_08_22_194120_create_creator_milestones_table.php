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
        Schema::create('creator_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('influencer_id')->constrained('influencers');
            $table->foreignId('creator_user_id')->constrained('users');
            $table->enum('milestone_type', ['posts_10','posts_50','likes_50']);
            $table->integer('milestone_value')->default(0);
            $table->decimal('reward', 10, 2);
            $table->boolean('credited')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creator_milestones');
    }
};
