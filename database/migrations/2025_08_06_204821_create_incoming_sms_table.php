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
        Schema::create('incoming_sms', function (Blueprint $table) {
            $table->id();
            $table->string('from'); // sender's number
            $table->string('to');   // your vonage number
            $table->text('text');   // message content
            $table->string('message_id')->nullable(); // Vonage message ID
            $table->string('received_at')->nullable(); // timestamp from Vonage
            $table->string('ip_address')->nullable(); // IP address of the sender
            $table->string('user_agent')->nullable(); // User agent of the sender
            $table->string('status')->default('received'); // status of the message
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_sms');
    }
};
