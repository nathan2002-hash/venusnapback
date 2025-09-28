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
        $countries = collect(app('rinvex.countries'))
            ->map(function ($country, $code) {
                return [
                    'name'       => $country->getName(),
                    'code'       => $code,
                    'phone_code' => $country->getCallingCode(),
                ];
            })->values();

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
