<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\RegistrationJob;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use App\Jobs\SendTwoFactorCodeJob;
use App\Jobs\SendPasswordRestCode;
use App\Jobs\LoginActivityJob;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function socialLogin(Request $request)
    {
        $request->validate([
            'provider' => 'required|string|in:google,facebook,apple',
            'access_token' => 'required|string',
            'provider_id' => 'required|string',
        ]);

        $userAgent = $request->header('User-Agent');
        $deviceinfo = $request->header('Device-Info');
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        try {
            $provider = $request->provider;
            $accessToken = $request->access_token;
            $providerId = $request->provider_id;

            // Verify social token (you might want to add more validation here)
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->user();

            if ($socialUser->getId() !== $providerId) {
                return response()->json([
                    'error' => 'Invalid social credentials'
                ], 401);
            }

            // Find existing user by provider
            $user = User::where('provider', $provider)
                        ->where('provider_id', $providerId)
                        ->first();

            // If user doesn't exist, try to find by email
            if (!$user && $socialUser->getEmail()) {
                $user = User::where('email', $socialUser->getEmail())->first();

                // If user exists but with different provider, update provider info
                if ($user) {
                    $user->update([
                        'provider' => $provider,
                        'provider_id' => $providerId,
                    ]);
                }
            }

            // Create new user if doesn't exist
            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Social User',
                    'email' => $socialUser->getEmail() ?? $providerId . '@' . $provider . '.com',
                    'username' => $this->generateUniqueUsername($socialUser->getName() ?? $socialUser->getNickname()),
                    'phone' => '',
                    'country_code' => '',
                    'partial_number' => '',
                    'country' => '',
                    'points' => '300',
                    'preference' => '1',
                    'timezone' => 'Africa/Lusaka',
                    'password' => Hash::make(Str::random(24)),
                    'provider' => $provider,
                    'provider_id' => $providerId,
                    'email_verified_at' => now(), // Social emails are typically verified
                ]);

                // Dispatch registration job
                RegistrationJob::dispatch($user, $userAgent, $deviceinfo, $ipaddress);
            }

            // Check account status
            if ($user->status === 'deletion') {
                return response()->json(['error' => 'Your account is queued for deletion.'], 403);
            }
            if ($user->status === 'locked') {
                return response()->json(['error' => 'Your account is locked. Contact support.'], 403);
            }
            if ($user->status !== 'active') {
                return response()->json(['error' => 'Account not active.'], 403);
            }

            // Create token
            $token = $user->createToken('authToken');

            // Log login activity
            LoginActivityJob::dispatch(
                $user,
                true,
                'You successfully logged into your account via ' . ucfirst($provider),
                'Social Login Successful',
                $userAgent,
                $ipaddress,
                $deviceinfo
            );

            // Handle 2FA - social login might bypass 2FA or you can keep it
            $authe = ($user->usersetting->tfa === null || $user->usersetting->tfa == 1) ? 1 : 0;
            if ($authe == 1) {
                $code = rand(100000, 999999);
                $user->tfa_code = Hash::make($code);
                $user->tfa_expires_at = now()->addMinutes(10);
                $user->save();
                SendTwoFactorCodeJob::dispatch($user, $code);
            }

            $profileUrl = $user->profile_compressed
                ? generateSecureMediaUrl($user->profile_compressed)
                : ($socialUser->getAvatar() ?? 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp');

            $preference = ($user->preference === null || $user->preference == 1) ? 1 : 0;

            return response()->json([
                'username' => (string) $user->username,
                'fullname' => $user->name,
                'token' => $token->accessToken,
                'preference' => (string) $preference,
                'profile' => $profileUrl,
                '2fa' => (string) $authe,
            ]);

        } catch (\Exception $e) {
            \Log::error('Social login failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'Social authentication failed',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    private function generateUniqueUsername($baseName)
    {
        $baseUsername = Str::slug($baseName);
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }
}
