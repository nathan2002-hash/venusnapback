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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('preference')->default(1);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status')->default('active');
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('country_code')->nullable();
            $table->string('partial_number')->nullable();
            $table->string('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('tfa_code')->nullable();
            $table->string('tfa_expires_at')->nullable();
            $table->string('points')->default(300);
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->string('profile_original', 2048)->nullable();
            $table->string('profile_compressed', 2048)->nullable();
            $table->string('cover_original', 2048)->nullable();
            $table->string('cover_compressed', 2048)->nullable();
            $table->string('reset_code')->nullable();
            $table->string('reset_expires_at')->nullable();
            $table->string('phone_code')->nullable();
            $table->string('phone_code_expires_at')->nullable();
            $table->string('email_code')->nullable();
            $table->string('email_code_expires_at')->nullable();
            $table->string('phone_verified_at')->nullable();
            $table->string('timezone')->default('UTC')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'two_factor_secret')) {
                $table->dropColumn('two_factor_secret');
            }
            if (Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->dropColumn('two_factor_recovery_codes');
            }
            if (Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->dropColumn('two_factor_confirmed_at');
            }
        });

        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
