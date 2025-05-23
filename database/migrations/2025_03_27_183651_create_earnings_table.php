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
        Schema::create('earnings', function (Blueprint $table) {
            $table->id();
            $table->string('album_id'); // Monetized album ID
            $table->string('post_id')->nullable(); // For reference
            $table->string('post_media_id')->nullable(); // Optional
            $table->decimal('earning', 13, 2)->default(0); // Default 0 even if no earnings
            $table->string('points')->nullable(); // view, like, support, ad_click
            $table->string('type')->nullable(); // view, like, support, ad_click
            $table->string('batch_id')->nullable(); // Unique batch reference
            $table->json('meta')->nullable(); // Metadata: full json of what happened
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earnings');
    }
};
