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
        Schema::create('ad_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id');
            $table->string('file_path');
            $table->string('file_path_compress');
            $table->string('status');
            $table->string('type');
            $table->text('object')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_media');
    }
};
