<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Jobs\RegistrationJob;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Activity;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SendTwoFactorCodeJob;
use App\Models\Account;
use App\Models\UserSetting;
use App\Jobs\LoginActivityJob;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function registerform()
    {
        return view('auth.register');
    }

    public function getCountries()
    {
        $response = Http::get('https://countriesnow.space/api/v0.1/countries/codes');

        if ($response->successful()) {
            $countries = collect($response->json()['data'])->map(function ($country) {
                return [
                    'name'       => $country['name'],
                    'code'       => $country['code'],
                    'phone_code' => str_replace('+', '', $country['dial_code']),
                ];
            })->values();

            return response()->json($countries);
        }

        return response()->json(['error' => 'Failed to fetch countries'], 500);
    }


    public function detectCountry(Request $request)
    {
        $clientIP = $request->header('do-connecting-ip');
        $response = Http::get("http://ip-api.com/json/{$clientIP}");

        if ($response->successful()) {
            $data = $response->json();
            return response()->json([
                'country' => $data['country'] ?? null,
                'country_code' => $data['countryCode'] ?? null,
            ]);
        }
    }

     public function register(Request $request)
    {
        // Verify reCAPTCHA first
         $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
         $ipaddress = is_string($realIp) ? $realIp : $request->ip();
         $recaptchaResponse = $this->verifyRecaptcha($request->input('g-recaptcha-response'), $request);

        if (!$recaptchaResponse['success'] || $recaptchaResponse['score'] < 0.5) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Security verification failed. Please try again.'
            ], 422);
        }

        $request->validate([
            'full_name'     => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => 'required|string|min:8|confirmed',
            'phone_number'  => ['required', 'regex:/^[0-9]{7,15}$/'],
            'country_code'  => 'required|string',
            'country'       => 'required|string',
        ]);

        $userAgent  = $request->header('User-Agent');
        $deviceinfo = $request->header('Device-Info');
        $realIp     = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress  = $realIp;

        // Clean up input
        $rawInput      = preg_replace('/[^0-9]/', '', $request->phone_number);
        $countryCode   = preg_replace('/[^0-9]/', '', $request->country_code);

        // Strip duplicated country code from start of phone number
        if (Str::startsWith($rawInput, $countryCode)) {
            $localPhone = substr($rawInput, strlen($countryCode));
        } else {
            $localPhone = $rawInput;
        }

        $fullPhone = $countryCode . $localPhone;

        // Check if a user already has this phone+password (full or local match)
        $existingUsers = User::get();

        foreach ($existingUsers as $user) {
            $existingPhone = preg_replace('/[^0-9]/', '', $user->phone);
            $existingCode  = preg_replace('/[^0-9]/', '', $user->country_code ?? '');

            $existingLocal = Str::startsWith($existingPhone, $existingCode)
                ? substr($existingPhone, strlen($existingCode))
                : $existingPhone;

            $fullMatch  = $existingPhone === $countryCode . $localPhone;
            $localMatch = $existingLocal === $localPhone;

            if (Hash::check($request->password, $user->password) && ($fullMatch || $localMatch)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Please choose a different strong password to continue.'
                ], 409);
            }
        }

        // Save user
        $user = User::create([
            'name'           => $request->full_name,
            'email'          => $request->email,
            'username'       => $request->full_name,
            'phone'          => $fullPhone,
            'country_code'   => $countryCode,
            'partial_number' => $localPhone,
            'country'        => $request->country,
            'points'         => '300',
            'preference'     => '1',
            'timezone'       => 'Africa/Lusaka',
            'password'       => Hash::make($request->password),
        ]);

        // Log the user in
        Auth::login($user);

        RegistrationJob::dispatch($user, $userAgent, $deviceinfo, $ipaddress);

        return response()->json([
            'status'  => 'success',
            'message' => 'Registration successful',
            'redirect_url' => '/onboard/welcome'
        ], 200);
    }

    /**
     * Verify reCAPTCHA token
     */
   private function verifyRecaptcha($token, $ip = null)
    {
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $token,
                'remoteip' => $ip
            ]);

            return $response->json();
        } catch (\Exception $e) {
            \Log::error('reCAPTCHA verification failed: ' . $e->getMessage());
            return ['success' => false];
        }
    }



    public function show(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            return view('dashboard', [
                'name' => $user->name,
                'email' => $user->email,
                'join_date' => $user->created_at->format('F j, Y'),
                'points' => $user->points ?? '300',
                'user' => $user
            ]);
        } else {
            // Fallback for non-authenticated users (shouldn't happen after registration)
            return view('dashboard', [
                'name' => $request->query('name', 'Creator')
            ]);
        }
    }

    /**
     * Redirect to provider for web login
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle provider callback for web login
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();

            $userAgent = request()->header('User-Agent');
            $deviceinfo = 'Web Browser';
            $realIp = request()->header('cf-connecting-ip') ?? request()->ip();
            $ipaddress = $realIp;

            // Reuse your existing social login logic
            $user = $this->findOrCreateUserFromSocial($socialUser, $provider, $userAgent, $deviceinfo, $ipaddress);

            // Log the user in using Laravel's session
            Auth::login($user, true);

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

            // Handle 2FA for web
            if ($user->usersetting && ($user->usersetting->tfa === null || $user->usersetting->tfa == 1)) {
                $code = rand(100000, 999999);
                $user->tfa_code = Hash::make($code);
                $user->tfa_expires_at = now()->addMinutes(10);
                $user->save();
                SendTwoFactorCodeJob::dispatch($user, $code);

                // Redirect to 2FA page for web
                return redirect('/two-factor-auth');
            }

            // Redirect based on preference for web
            if ($user->preference == 1) {
                return redirect('/categories');
            } else {
                return redirect('/home');
            }

        } catch (\Exception $e) {
            \Log::error('Web social login failed: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Social authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Reusable method to find or create user from social provider
     * This extracts the common logic from your API method
     */
    private function findOrCreateUserFromSocial($socialUser, $provider, $userAgent, $deviceinfo, $ipaddress)
    {
        // Find existing user by provider
        $user = User::where('provider', $provider)
                    ->where('provider_id', $socialUser->getId())
                    ->first();

        // If user doesn't exist, try to find by email
        if (!$user && $socialUser->getEmail()) {
            $user = User::where('email', $socialUser->getEmail())->first();

            // If user exists but with different provider, update provider info
            if ($user) {
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);

                // Ensure user settings exist for existing users
                $this->ensureUserSettingsExist($user);
            }
        }

        $profileAvatarUrl = $socialUser->getAvatar();

        // Create new user if doesn't exist
        if (!$user) {
            // Detect country and timezone from IP
            $locationData = $this->detectCountryAndTimezone($ipaddress);

            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Social User',
                'email' => $socialUser->getEmail() ?? $socialUser->getId() . '@' . $provider . '.com',
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
                'provider_id' => $socialUser->getId(),
                'email_verified_at' => now(),
                'status' => 'active',
            ]);

            // Run registration setup
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

        // Check account status and throw exceptions for web flow
        if ($user->status === 'deletion') {
            throw new \Exception('Your account is queued for deletion.');
        }

        if ($user->status === 'locked') {
            throw new \Exception('Your account is locked. Contact support.');
        }

        if ($user->status !== 'active') {
            throw new \Exception('Account not active. Current status: ' . $user->status);
        }

        return $user;
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
