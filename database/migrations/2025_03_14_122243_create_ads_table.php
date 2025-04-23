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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('adboard_id');
            $table->string('category')->nullable();
            $table->string('ag_type')->nullable();
            $table->string('cta_name');
            $table->string('cta_link');
            $table->string('cta_type');
            $table->string('status');
            $table->text('ag_description')->nullable();
            $table->text('description')->nullable();
            $table->enum('target', ['all_region', 'specify'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
