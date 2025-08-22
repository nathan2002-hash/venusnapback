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
        Schema::create('influencer_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('influencer_id')->constrained('influencers');
            $table->foreignId('referred_user_id')->constrained('users');
            $table->foreignId('post_id')->constrained('posts');
            $table->decimal('reward', 10, 2);
            $table->enum('milestone_type', ['post_click','creator_upload','likes','milestone_bonus']);
            $table->integer('milestone_value')->default(0);
            $table->boolean('credited')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('influencer_referrals');
    }
};
