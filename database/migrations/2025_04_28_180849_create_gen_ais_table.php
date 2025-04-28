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
        Schema::create('gen_ais', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('provider')->nullable();
            $table->string('provider_credit')->nullable();
            $table->string('venusnap_points')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_path_compress')->nullable();
            $table->string('original_description')->nullable();
            $table->string('edited_description')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gen_ais');
    }
};
