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
        Schema::create('venusnap_systems', function (Blueprint $table) {
            $table->id();
            // Total money currently in the system (e.g., from advertisers)
            $table->decimal('system_money', 15, 3)->default(0);
            // Amount of points that can still be allocated to creators
            $table->integer('reserved_points')->default(0);
            // Conversion rate: how many points per $1
            $table->integer('points_per_dollar')->default(1000);
            // Example: points earned per discovery in Explore
            $table->integer('points_per_discovery')->default(2);
            // Points per milestone reward
            $table->integer('points_per_milestone')->default(4000);
            // Points per admire (feed engagement)
            $table->integer('points_per_admire')->default(1);
            // Total points spent to creators so far
            $table->integer('total_points_spent')->default(0);
            // Optional: track total points earned in system (for statistics)
            $table->integer('total_points_earned')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venusnap_systems');
    }
};
