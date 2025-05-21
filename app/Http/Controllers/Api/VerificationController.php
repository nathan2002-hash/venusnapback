<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendVerificationCodeJob;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function sendPhoneVerificationCode()
    {
        $user = Auth::user();
        try {
            // Generate a 6-digit code
            $code = mt_rand(100000, 999999);

            // Set expiration time (e.g., 15 minutes from now)
            $expiresAt = Carbon::now()->addMinutes(15)->toDateTimeString();

            // Update user record with the code and expiration time
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'phone_code' => $code,
                    'phone_code_expires_at' => $expiresAt,
                    // Don't reset phone_verified_at here - only when actually verified
                ]);

            // Dispatch the job
            SendVerificationCodeJob::dispatch(
                $user,
                $code,
                'phone',
                "Your verification code is: {$code}"
            );

            return true;
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Failed to send phone verification code: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email verification code
     */
    public function sendEmailVerificationCode()
    {
        $user = Auth::user();
        try {
            // Generate a 6-digit code
            $code = mt_rand(100000, 999999);

            // Set expiration time (e.g., 15 minutes from now)
            $expiresAt = Carbon::now()->addMinutes(15)->toDateTimeString();

            // Update user record with the code and expiration time
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'email_code' => $code,
                    'email_code_expires_at' => $expiresAt,
                ]);

            // Dispatch the job
            SendVerificationCodeJob::dispatch(
                $user,
                $code,
                'email'
            );

            return true;
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Failed to send email verification code: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify phone code
     */
    public function verifyPhone(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $now = Carbon::now()->toDateTimeString();

        // Check if code matches and isn't expired
        $verified = DB::table('users')
            ->where('id', $user->id)
            ->where('phone_code', $request->code)
            ->where('phone_code_expires_at', '>', $now)
            ->exists();

        if ($verified) {
            // Update verification status
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'phone_verified_at' => $now,
                    'phone_code' => null,
                    'phone_code_expires_at' => null,
                ]);

            return response()->json(['message' => 'Phone number verified successfully']);
        }

        return response()->json(['message' => 'Invalid or expired verification code'], 400);
    }

    /**
     * Verify email code
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $now = Carbon::now()->toDateTimeString();

        // Check if code matches and isn't expired
        $verified = DB::table('users')
            ->where('id', $user->id)
            ->where('email_code', $request->code)
            ->where('email_code_expires_at', '>', $now)
            ->exists();

        if ($verified) {
            // Update verification status
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'email_verified_at' => $now,
                    'email_code' => null,
                    'email_code_expires_at' => null,
                ]);

            return response()->json(['message' => 'Email verified successfully']);
        }

        return response()->json(['message' => 'Invalid or expired verification code'], 400);
    }
}
