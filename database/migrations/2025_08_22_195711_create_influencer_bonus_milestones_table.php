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
        Schema::create('influencer_bonus_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('influencer_id')->constrained('influencers');
            $table->enum('milestone_type', ['total_users_50','total_creators_10']);
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
        Schema::dropIfExists('influencer_bonus_milestones');
    }
};
