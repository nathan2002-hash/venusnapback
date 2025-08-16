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
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'sms' or 'email'
            $table->string('subject');
            $table->text('body');
            $table->string('recipient_type'); // 'user' or 'album'
            $table->unsignedBigInteger('user_id')->nullable(); // if sending to specific user
            $table->unsignedBigInteger('album_id')->nullable(); // if sending to album users
            $table->string('attachment_path')->nullable(); // for emails
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->unsignedBigInteger('sent_by'); // admin user who sent it
            $table->string('sms_provider')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('album_id')->references('id')->on('albums');
            $table->foreign('sent_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
