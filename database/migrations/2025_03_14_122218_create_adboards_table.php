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
        Schema::create('adboards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('album_id');
            $table->string('status');
            $table->integer('points');
            $table->integer('budget');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adboards');
    }
};
