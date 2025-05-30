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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 3)->unique(); // ISO 3166-1 alpha-3 code
            $table->string('sample_phone')->nullable(); // e.g. "260970000000"
            $table->integer('phone_number_length')->nullable(); // e.g. 12
            $table->string('continent_id'); // ISO 3166-1 alpha-3 code
            $table->string('phone_code')->nullable(); // International dialing code
            $table->string('capital')->nullable(); // Capital city
            $table->string('currency')->nullable(); // Currency name
            $table->string('currency_code', 3)->nullable(); // Currency code (ISO 4217)
            $table->string('flag')->nullable(); // URL to the flag image
            $table->text('description')->nullable(); // Additional description or notes
            $table->boolean('is_active')->default(true); // Active status
            $table->boolean('is_verified')->default(false); // Verification status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
