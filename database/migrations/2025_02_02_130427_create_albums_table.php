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
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Owner of album
            $table->enum('type', ['personal', 'creator', 'business']);

            // Common fields for all albums
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('visibility', ['private', 'public', 'exclusive'])->default('private');
            $table->string('thumbnail_original')->nullable(); // Path to thumbnail (Personal & Creator use this)
            $table->string('thumbnail_compressed')->nullable(); // Path to thumbnail (Personal & Creator use this)
            $table->string('is_verified')->default(0);
            $table->string('category_id')->nullable();

            // Personal-specific (nothing extra in your case)

            // Creator-specific fields
            $table->date('release_date')->nullable();
            $table->string('content_type')->nullable(); // E.g., photo, video, audio
            $table->json('tags')->nullable(); // Store tags as JSON array
            $table->boolean('allow_comments')->default(true);
            $table->boolean('enable_rating')->default(true);

            // Business-specific fields
            $table->boolean('is_paid_access')->default(false);
            $table->decimal('price', 10, 2)->nullable(); // If paid access
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('location')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('business_logo_original')->nullable(); // Path to logo (for business)
            $table->string('cover_image_original')->nullable(); // Path to cover image (for business)
            $table->string('business_logo_compressed')->nullable(); // Path to logo (for business)
            $table->string('cover_image_compressed')->nullable(); // Path to cover image (for business)
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
