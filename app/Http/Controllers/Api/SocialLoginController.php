<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
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
        $profileAvatarUrl = $socialUser->getAvatar();

        // Create new user if doesn't exist
        if (!$user) {
            $isNewUser = true;

            // Detect country and timezone from IP
            $locationData = $this->detectCountryAndTimezone($ipaddress);

            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Social User',
                'email' => $socialUser->getEmail() ?? $providerId . '@' . $provider . '.com',
                'username' => $this->generateUniqueUsername($socialUser->getName() ?? $socialUser->getNickname()),
                'phone' => '',
                'country_code' => $locationData['country_code'] ?? '',
                'partial_number' => '',
                'country' => $locationData['country'] ?? '',
                'points' => '300',
                'preference' => '1',
                'timezone' => $locationData['timezone'] ?? 'Africa/Lusaka',
                'password' => Hash::make(Str::random(24)),
                'provider' => $provider,
                'provider_id' => $providerId,
                'email_verified_at' => now(),
                'status' => 'active',
            ]);

            // For social login, run the registration setup immediately
            $this->runRegistrationSetup($user, $userAgent, $deviceinfo, $ipaddress);

            // Download and store profile image for new users
            if ($profileAvatarUrl) {
                $this->downloadAndStoreProfileImage($user, $profileAvatarUrl, $provider);
            }
        } else {
            // For existing users, update profile image if they don't have one
            if (!$user->profile_original && $profileAvatarUrl) {
                $this->downloadAndStoreProfileImage($user, $profileAvatarUrl, $provider);
            }
        }

        // Double-check user status and settings
        if ($user->status !== 'active') {
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

        // Generate profile URL - use the compressed version if available
        $profileUrl = $user->profile_compressed
            ? generateSecureMediaUrl($user->profile_compressed)
            : ($profileAvatarUrl ?? 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp');

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

private function detectCountryAndTimezone($ipaddress)
{
    try {
        $response = Http::get("http://ip-api.com/json/{$ipaddress}");

        if ($response->successful()) {
            $data = $response->json();

            if ($data['status'] === 'success') {
                return [
                    'country' => $data['country'] ?? null,
                    'country_code' => $data['countryCode'] ?? null,
                    'timezone' => $data['timezone'] ?? 'Africa/Lusaka',
                    'city' => $data['city'] ?? null,
                    'region' => $data['region'] ?? null,
                ];
            }
        }
    } catch (\Exception $e) {
        \Log::error("Failed to detect country/timezone for IP {$ipaddress}: " . $e->getMessage());
    }

    // Return default values if API fails
    return [
        'country' => null,
        'country_code' => null,
        'timezone' => 'Africa/Lusaka',
        'city' => null,
        'region' => null,
    ];
}

/**
 * Download and store social profile image in S3 (no compression)
 */
private function downloadAndStoreProfileImage(User $user, string $imageUrl, string $provider)
{
    try {
        // Download the image
        $client = new \GuzzleHttp\Client();
        $response = $client->get($imageUrl, [
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to download image. HTTP status: ' . $response->getStatusCode());
        }

        $imageContent = $response->getBody();
        $imageSize = strlen($imageContent);

        // Validate image size (max 10MB)
        if ($imageSize > 10 * 1024 * 1024) {
            throw new \Exception('Image too large: ' . $imageSize . ' bytes');
        }

        // Get image extension from URL or content type
        $extension = $this->getImageExtension($imageUrl, $response->getHeaderLine('Content-Type'));

        // Generate unique filenames
        $timestamp = now()->timestamp;
        $filename = "uploads/profiles/originals/profile/{$user->id}_{$timestamp}.{$extension}";
        $compressedFilename = "uploads/profiles/compressed/profile/{$user->id}_{$timestamp}.{$extension}";

        // Store the same image in both original and compressed columns
        Storage::disk('s3')->put($filename, $imageContent, 'public');

        // Update user record - store same image in both columns
        $user->update([
            'profile_original' => $filename,
            'profile_compressed' => $compressedFilename, // Same file for both
        ]);

        \Log::info("Profile image stored for user {$user->id} from {$provider} - File: {$filename}");

    } catch (\Exception $e) {
        \Log::error("Failed to download profile image for user {$user->id}: " . $e->getMessage());
        // Don't throw the error - we don't want to break the login process
    }
}

/**
 * Get image extension from URL or content type
 */
private function getImageExtension(string $imageUrl, string $contentType): string
{
    // Try to get extension from URL
    $path = parse_url($imageUrl, PHP_URL_PATH);
    if ($path && preg_match('/\.(jpg|jpeg|png|gif|webp|bmp)$/i', $path, $matches)) {
        return strtolower($matches[1]);
    }

    // Get extension from content type
    $contentTypeToExtension = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/bmp' => 'bmp',
    ];

    return $contentTypeToExtension[$contentType] ?? 'jpg';
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
