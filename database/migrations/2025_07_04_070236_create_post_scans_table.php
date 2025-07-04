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
        Schema::create('post_scans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_media_id');
            $table->string('thredhold');
            $table->boolean('detected')->default(false);
            $table->json('raw_result')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_scans');
    }
};
