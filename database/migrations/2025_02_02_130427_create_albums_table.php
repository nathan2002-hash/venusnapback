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
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id'); // ONE Artboard per user
            $table->string('name')->unique(); // Artboard name (e.g., "John's Artboard")
            $table->string('slug')->unique(); // URL-friendly name
            $table->text('description')->nullable(); // Short bio or description
            $table->string('type'); // Artboard category
            $table->string('is_verified')->default(0); // Verification status
            $table->string('visibility')->default('public'); // public, private, followers-only
            $table->string('logo')->nullable(); // Artboard logo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
