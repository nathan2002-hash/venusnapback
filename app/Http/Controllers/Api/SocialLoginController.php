<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use App\Jobs\SendTwoFactorCodeJob;
use App\Jobs\SendPasswordRestCode;
use App\Jobs\LoginActivityJob;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendWelcomeEmail;
use Illuminate\Support\Facades\Http;
use App\Models\Activity;

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

        // Verify social token using userFromToken
        $socialUser = Socialite::driver($provider)
            ->stateless()
            ->userFromToken($accessToken);

        // Validate provider ID
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

                // Ensure user settings exist for existing users
                $this->ensureUserSettingsExist($user);
            }
        }

        $isNewUser = false;
        // Create new user if doesn't exist
        if (!$user) {
            $isNewUser = true;
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
                'email_verified_at' => now(),
                'status' => 'active', // Explicitly set status to active
            ]);

            // For social login, run the registration setup immediately instead of queuing
            $this->runRegistrationSetup($user, $userAgent, $deviceinfo, $ipaddress);
        }

        // Double-check user status and settings
        if ($user->status !== 'active') {
            // If status is not active, activate it
            $user->update(['status' => 'active']);
        }

        // Ensure user settings exist
        $this->ensureUserSettingsExist($user);

        // Check account status
        if ($user->status === 'deletion') {
            return response()->json([
                'error' => 'Your account is queued for deletion.',
                'account_status' => 'deletion'
            ], 403);
        }

        if ($user->status === 'locked') {
            return response()->json([
                'error' => 'Your account is locked. Contact support.',
                'account_status' => 'locked'
            ], 403);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'error' => 'Account not active.',
                'account_status' => $user->status,
                'message' => 'Current account status: ' . $user->status
            ], 403);
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

        // Handle 2FA - check if user setting exists
        $authe = 0;
        if ($user->usersetting) {
            $authe = ($user->usersetting->tfa === null || $user->usersetting->tfa == 1) ? 1 : 0;
        }

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
            'is_new_user' => $isNewUser,
        ]);

    } catch (\Exception $e) {
        \Log::error('Social login failed: ' . $e->getMessage());

        return response()->json([
            'error' => 'Social authentication failed',
            'message' => $e->getMessage()
        ], 401);
    }
}

/**
 * Ensure user settings exist for social login users
 */
private function ensureUserSettingsExist(User $user)
{
    // Check if user setting exists, if not create it
    if (!$user->usersetting) {
        $usersetting = new UserSetting();
        $usersetting->user_id = $user->id;
        $usersetting->sms_alert = 1;
        $usersetting->history = 1;
        $usersetting->save();

        // Reload the relationship
        $user->load('usersetting');
    }

    // Check if account exists, if not create it
    if (!$user->account) {
        $account = Account::firstOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'account_balance' => 0.00,
                'available_balance' => 0.00,
                'monetization_status' => 'inactive',
                'payout_method' => 'paypal',
                'country' => $user->country,
                'currency' => 'USD',
                'paypal_email' => $user->email
            ]
        );
    }
}

private function runRegistrationSetup(User $user, $userAgent, $deviceinfo, $ipaddress)
{
    try {
        $timezone = 'Africa/Lusaka'; // default fallback

        // Get location info from IP
        $response = Http::get("http://ipinfo.io/{$ipaddress}/json");
        if ($response->successful()) {
            $data = $response->json();
            if (!empty($data['timezone'])) {
                $timezone = $data['timezone'];
            }

            // Update user timezone
            $user->timezone = $timezone;
            $user->save();
        }
    } catch (\Exception $e) {
        \Log::error("Failed to fetch IP info for {$ipaddress}: " . $e->getMessage());
    }

    // Create activity log
    $activity = new Activity();
    $activity->title = 'Account Created via Social Login';
    $activity->description = 'Your account has been created via ' . $user->provider;
    $activity->source = 'Social Registration';
    $activity->user_id = $user->id;
    $activity->status = true;
    $activity->user_agent = $userAgent;
    $activity->device_info = $deviceinfo;
    $activity->ipaddress = $ipaddress;
    $activity->save();

    // Create user settings
    $usersetting = new UserSetting();
    $usersetting->user_id = $user->id;
    $usersetting->sms_alert = 1;
    $usersetting->history = 1;
    $usersetting->save();

    // Create account
    $account = Account::firstOrCreate(
        ['user_id' => $user->id],
        [
            'user_id' => $user->id,
            'account_balance' => 0.00,
            'available_balance' => 0.00,
            'monetization_status' => 'inactive',
            'payout_method' => 'paypal',
            'country' => $user->country,
            'currency' => 'USD',
            'paypal_email' => $user->email
        ]
    );

    // Send welcome email via queue
    //SendWelcomeEmail::dispatch($user, $deviceinfo, $ipaddress);
    // Send notification SMS
    // $this->sendWithVonage('260970333596', 'New Venusnap user registered via ' . $user->provider . ': ' . $user->name);
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
