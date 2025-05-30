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
        Schema::create('sms_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('driver'); // e.g. 'beem', 'twilio', etc.
            $table->string('price')->nullable(); // Price per SMS
            $table->string('api_key')->nullable(); // API key for the SMS provider
            $table->string('api_secret')->nullable(); // API secret for the SMS provider
            $table->string('sender_id')->nullable(); // API secret for the SMS provider
            $table->json('meta')->nullable(); //storing other authentication details for some because they differ
            $table->string('country_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_providers');
    }
};
