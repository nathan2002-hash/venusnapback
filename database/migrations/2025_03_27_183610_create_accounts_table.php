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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->decimal('account_balance', 13,2);
            $table->decimal('available_balance', 13,2);
            $table->string('monetization_status')->default(0);
            $table->string('payout_method');
            $table->string('currency');
            $table->string('country')->nullable();

            // PayPal-specific field
            $table->string('paypal_email')->nullable();

            // Mobile Money field
            $table->string('phone_no')->nullable();
            $table->string('account_name')->nullable();
            $table->string('network')->nullable();

            // Bank transfer fields
            $table->string('account_holder_name')->nullable();  // Bank account holder's name
            $table->string('account_number')->nullable();  // Bank account number
            $table->string('account_type')->nullable();  // Bank account number
            $table->string('bank_name')->nullable();  // Bank name
            $table->string('bank_address')->nullable();  // Bank branch (optional)
            $table->string('swift_code')->nullable();  // SWIFT/BIC code for international transfers
            $table->string('routing_number')->nullable();  // IBAN for international bank transfers

            $table->string('reference_no')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
