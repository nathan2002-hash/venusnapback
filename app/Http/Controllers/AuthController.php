<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function registerform()
    {
        return view('auth.register');
    }

    public function getCountries()
    {
        // You can get countries from database or a package
        $countries = [
            ['name' => 'United States', 'code' => 'US', 'phone_code' => '1'],
            ['name' => 'United Kingdom', 'code' => 'GB', 'phone_code' => '44'],
            ['name' => 'Canada', 'code' => 'CA', 'phone_code' => '1'],
            ['name' => 'Australia', 'code' => 'AU', 'phone_code' => '61'],
            ['name' => 'Germany', 'code' => 'DE', 'phone_code' => '49'],
            ['name' => 'France', 'code' => 'FR', 'phone_code' => '33'],
            ['name' => 'India', 'code' => 'IN', 'phone_code' => '91'],
            ['name' => 'Japan', 'code' => 'JP', 'phone_code' => '81'],
            ['name' => 'Brazil', 'code' => 'BR', 'phone_code' => '55'],
            ['name' => 'South Africa', 'code' => 'ZA', 'phone_code' => '27'],
        ];

        return response()->json($countries);
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
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'country' => 'required|string',
            'country_code' => 'required|string',
            'phone_number' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'accept_terms' => 'required|accepted',
            'accept_privacy' => 'required|accepted',
        ]);

        // Your registration logic here
        // ...

        return redirect()->route('login')->with('success', 'Registration successful!');
    }

}
