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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->decimal('amount', 13,2);
            $table->string('payment_method');
            $table->string('currency');
            $table->string('processor');
            $table->string('payment_no')->nullable();
            $table->string('status');
            $table->string('payment_session_id');
            $table->string('purpose');
            $table->longText('description');
            $table->longText('status_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
