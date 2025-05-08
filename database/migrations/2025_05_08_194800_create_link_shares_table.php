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
        Schema::create('link_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_media_id')->nullable()->constrained('post_media')->onDelete('set null');
            $table->string('share_method'); // 'direct', 'whatsapp', 'facebook', etc.
            $table->string('share_url');
            $table->string('short_code')->unique()->nullable(); // For shortened URLs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_shares');
    }
};
