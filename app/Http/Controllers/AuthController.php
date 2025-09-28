<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Jobs\RegistrationJob;

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
}
