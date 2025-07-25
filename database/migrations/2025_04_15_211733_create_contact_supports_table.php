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
        Schema::create('contact_supports', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('category');
            $table->string('topic');
            $table->longText('description');
            $table->string('priority');
            $table->string('status');
            $table->string('resolved_at');
            $table->string('resolved_by');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_supports');
    }
};
