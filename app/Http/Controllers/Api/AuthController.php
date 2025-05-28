<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Album;
use GuzzleHttp\Client;
use App\Models\Artwork;
use App\Models\Activity;
use App\Models\Artboard;
use Illuminate\Http\Request;
use App\Jobs\RegistrationJob;
use App\Jobs\SendPasswordRestCode;
use App\Jobs\LoginActivityJob;
use App\Jobs\ChangePasswordJob;
use App\Mail\TwoFactorCodeMail;
use App\Jobs\SendTwoFactorCodeJob;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Client as PassportClient;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'login' => 'required|string',
            'password' => 'required',
            'type' => 'sometimes|in:email,phone'
        ]);

        $userAgent = $request->header('User-Agent');
        $deviceinfo = $request->header('Device-Info');
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        // Determine login type and prepare credentials
        $login = $request->input('login');
        $type = $request->input('type') ?? (filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone');

        $credentials = ['password' => $request->password];
        $loginField = $type === 'email' ? 'email' : 'phone';
        $credentials[$loginField] = $this->sanitizeLoginInput($login, $type);

        //Attempt authentication
        if (!Auth::attempt($credentials)) {
            $user = User::where('email', $login)
                    ->orWhere('phone', $this->sanitizePhone($login))
                    ->first();

            if ($user) {
                LoginActivityJob::dispatch(
                    $user,
                    false,
                    'Failed login attempt due to incorrect password.',
                    'Login Failed',
                    $userAgent,
                    $ipaddress,
                    $deviceinfo
                );
            }
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

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

        // Log successful login
        LoginActivityJob::dispatch(
            $user,
            true,
            'You successfully logged into your account',
            'Login Successful',
            $userAgent,
            $ipaddress,
            $deviceinfo
        );

        // Handle 2FA if enabled
        $authe = ($user->usersetting->tfa === null || $user->usersetting->tfa == 1) ? 1 : 0;
        if ($authe == 1) {
            $code = rand(100000, 999999);
            $user->tfa_code = Hash::make($code);
            $user->tfa_expires_at = now()->addMinutes(10);
            $user->save();
            SendTwoFactorCodeJob::dispatch($user, $code);
        }

        // Prepare response
        $profileUrl = $user->profile_compressed
            ? Storage::disk('s3')->url($user->profile_compressed)
            : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp';

        $preference = ($user->preference === null || $user->preference == 1) ? 1 : 0;

        return response()->json([
            'username' => (string) $user->username,
            'fullname' => $user->name,
            'token' => $token->accessToken,
            'preference' => (string) $preference,
            'profile' => $profileUrl,
            '2fa' => (string) $authe,
        ]);
    }

    protected function sanitizeLoginInput($input, $type)
    {
        return $type === 'phone'
            ? preg_replace('/[^0-9]/', '', $input)
            : $input;
    }

    protected function sanitizePhone($phone)
    {
        $sanitized = preg_replace('/[^0-9]/', '', $phone);
        return str_starts_with($sanitized, '0') ? substr($sanitized, 1) : $sanitized;
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'required|string',
            'country_code' => 'required|string', // e.g., 260
        ]);

        $userAgent = $request->header('User-Agent');
        $deviceinfo = $request->header('Device-Info');
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        // Sanitize phone number
        $cleanNumber = preg_replace('/[^0-9]/', '', $request->phone_number);
        $countryCode = preg_replace('/[^0-9]/', '', $request->country_code);
        $fullPhone = '+' . $countryCode . $cleanNumber;

        // Get local part (without country code)
        $localPhone = $cleanNumber;

        // Check for conflicts
        $existingUsers = User::all();
        foreach ($existingUsers as $user) {
            $existingPhone = preg_replace('/[^0-9]/', '', $user->phone);
            $existingLocal = ltrim($existingPhone, $countryCode); // assume country code length match

            if (
                Hash::check($request->password, $user->password) &&
                (
                    $existingPhone === $countryCode . $localPhone || // full match
                    $existingLocal === $localPhone                  // local match
                )
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Phone number and password already used by someone. Try a different password.'
                ], 409);
            }
        }

        // Save user
        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'username' => $request->full_name,
            'phone' => $fullPhone,
            'country' => $countryCode,
            'points' => '300',
            'preference' => '1',
            'password' => Hash::make($request->password),
        ]);

        RegistrationJob::dispatch($user, $userAgent, $deviceinfo, $ipaddress);

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful'
        ], 200);
    }


    public function riegister(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $userAgent = $request->header('User-Agent');
        $deviceinfo = $request->header('Device-Info');
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'username' => $request->full_name,
            'phone' => $request->phone_number,
            'country' => $request->country,
            'points' => '300',
            'preference' => '1',
            'password' => Hash::make($request->password),
        ]);

        RegistrationJob::dispatch($user, $userAgent, $deviceinfo, $ipaddress);

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful'
        ], 200);
    }


    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],  // must pass 'new_password_confirmation' too
        ]);

        $user = Auth::user();
        $userAgent = $request->header('User-Agent');
        $deviceinfo = $request->header('Device-Info');
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        if (!Hash::check($request->current_password, $user->password)) {
            ChangePasswordJob::dispatch($user, $request->current_password, $request->new_password, $userAgent, $ipaddress, $deviceinfo);
            return response()->json([
                'message' => 'Current password is incorrect',
                'success' => false,
            ], 422);
        }
        ChangePasswordJob::dispatch($user, $request->current_password, $request->new_password, $userAgent, $deviceinfo, $ipaddress);
        return response()->json([
            'message' => 'Password updated successfully',
            'success' => true,
        ]);
    }

    public function getLoginActivities(Request $request)
    {
        // Fetch activities where type is 'authentication'
        $activities = Activity::where('source', 'Authentication')
            ->where('user_id', $request->user()->id) // Fetch activities for the logged-in user
            ->select('device_info', 'ipaddress', 'created_at', 'title')
            ->orderBy('created_at', 'desc') // Order by latest activities
            ->paginate(10); // Paginate with 10 items per page

        // Add location to each activity
        $activities->transform(function ($activity) {
            $ip = $activity->ipaddress;
            if ($ip) {
                $location = $this->getLocationFromIP($ip); // Get location from IP
                $activity->location = $location;
            } else {
                $activity->location = 'Unknown Location'; // Handle missing IP address
            }
            return $activity;
        });

        return response()->json(['activities' => $activities->items()]);
    }

    public function fetchPasswordActivities(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Fetch the last 5 password update activities
        $activities = Activity::where('user_id', $user->id)
            ->where('source', 'Authentication')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get(['title', 'status', 'device_info', 'created_at', 'ipaddress']); // Include 'ipaddress'

        // Format the activities
        $formattedActivities = $activities->map(function ($activity) {
            $ip = $activity->ipaddress;
            $location = $ip ? $this->getLocationFromIP($ip) : 'Unknown Location'; // Get location from IP

            return [
                'action' => $activity->title,
                'time' => $activity->created_at->format('Y-m-d H:i:s'),
                'status' => $activity->status,
                'location' => $location, // Add location to the response
                'device' => $activity->device_info,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedActivities,
        ]);
    }

    // Helper function to get location from IP
    private function getLocationFromIP($ip)
    {
        try {
            $response = Http::get("http://ipinfo.io/{$ip}/json");
            if ($response->successful()) {
                $data = $response->json();
                return $data['city'] . ', ' . $data['country'];
            }
        } catch (\Exception $e) {
            // Log the error (optional)
            Log::error("Failed to resolve location for IP {$ip}: " . $e->getMessage());
        }
        return 'Unknown Location';
    }

    public function verify2FA(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = Auth::user(); // Get authenticated user

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$user->tfa_code || !$user->tfa_expires_at) {
            return response()->json(['error' => 'No 2FA code found'], 400);
        }

        // Check if the code matches and hasn't expired
        if (Hash::check($request->code, $user->tfa_code) && Carbon::now()->lt($user->tfa_expires_at)) {
            // Disable 2FA for this session
            $user->tfa_code = null;
            $user->tfa_expires_at = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => '2FA verification successful.',
            ]);
        }

        return response()->json(['error' => 'Invalid or expired 2FA code'], 400);
    }

    public function resend2FA(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $code = rand(100000, 999999); // Generate new 6-digit code

        $user->tfa_code = Hash::make($code);
        $user->tfa_expires_at = now()->addMinutes(10);
        $user->save();

        SendTwoFactorCodeJob::dispatch($user, $code);
        return response()->json([
            'success' => true,
            'message' => 'A new 2FA code has been sent to your email.',
        ]);
    }


    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'action' => 'required|string'
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Incorrect password'], 401);
        }

        // Use usersetting->tfa to determine 2FA status
        $authe = ($user->usersetting->tfa === null || $user->usersetting->tfa == 1) ? 1 : 0;

        return response()->json([
            'has_2fa' => (bool) $authe,
            'otp_sent' => false
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'otp' => 'nullable|digits:6'
        ]);

        $user = $request->user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Incorrect password'], 401);
        }

       //$tfaEnabled = ($user->usersetting->tfa === null || $user->usersetting->tfa == 1);

        //if ($tfaEnabled) {
            //if (!$request->otp || $user->tfa_code != $request->otp) {
                //return response()->json(['message' => 'Invalid 2FA code'], 401);
            //}
        //}

        $user->update([
            'status' => 'deletion',
        ]);

        // Send confirmation email
        //Mail::to($user->email)->send(new AccountDeletionMail());

        return response()->json(['message' => 'Account deletion scheduled']);
    }

    public function sendOTP(Request $request)
    {
        $user = $request->user();

        // Generate and save OTP
        $code = rand(100000, 999999);
        $user->update([
            'tfa_code' => $code,
            'tfa_expires_at' => now()->addMinutes(10)
        ]);

        // Send email with OTP
        SendTwoFactorCodeJob::dispatch($user, $code);
        return response()->json(['message' => 'OTP sent']);
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
            'action' => 'required|string'
        ]);

        $user = $request->user();

        if ($user->tfa_expires_at < now() ||
            $user->tfa_code != $request->otp) {
            return response()->json(['message' => 'Invalid or expired code'], 401);
        }

        // Clear OTP after successful verification
        $user->update([
            'tfa_code' => null,
            'tfa_expires_at' => null
        ]);

        return response()->json(['message' => 'OTP verified']);
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        $code = random_int(100000, 999999);
        $expiry = Carbon::now()->addMinutes(10);

        $user->reset_code = $code;
        $user->reset_code_expire = $expiry;
        $user->save();

        // Dispatch the email sending job
        SendPasswordRestCode::dispatch($user->email, $code);

        return response()->json([
            'message' => 'Reset code sent successfully',
            'email' => $user->email,
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reset_code' => 'required|numeric',
            'password' => 'required|min:6|confirmed', // expects password_confirmation too
        ]);

        $user = User::where('email', $request->email)
                    ->where('reset_code', $request->reset_code)
                    ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid email or code'], 404);
        }

        if (!$user->reset_code_expire || Carbon::now()->greaterThan($user->reset_code_expire)) {
            return response()->json(['message' => 'Reset code has expired'], 410);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->reset_code = null;
        $user->reset_code_expire = null;
        $user->save();

        return response()->json(['message' => 'Password reset successfully'], 200);
    }
}
