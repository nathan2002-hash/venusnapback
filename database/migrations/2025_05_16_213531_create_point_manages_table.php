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
        Schema::create('point_manages', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('manage_by');
            $table->integer('points');
            $table->string('reason');
            $table->string('type'); //either remove/add
            $table->string('status');
            $table->json('metadata')->nullable();
            $table->integer('balance_after');
            $table->longText('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_manages');
    }
};
