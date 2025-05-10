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
        Schema::create('app_messages', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('info'); // maintenance, update, info, etc.
            $table->string('title');
            $table->text('content');
            $table->string('image_path')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_action')->nullable();
            $table->boolean('dismissible')->default(true);
            $table->boolean('track_actions')->default(false);
            $table->json('platforms')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_messages');
    }
};
