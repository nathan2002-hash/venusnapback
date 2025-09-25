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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('conversation_id');
            $table->enum('direction', ['inbound', 'outbound']); // from user or from us
            $table->string('message_id')->nullable(); // Vonage message UUID
            $table->text('text')->nullable();
            $table->json('payload')->nullable(); // raw data (attachments, etc.)
            $table->timestamp('received_at')->nullable();
            $table->string('user_id'); // whatsapp or sms
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
