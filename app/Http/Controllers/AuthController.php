<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showRegistrationForm()
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
        try {
            // Get client IP
            $clientIP = $request->ip();

            // For local development, use a test IP or service
            if ($clientIP === '127.0.0.1' || $clientIP === '::1') {
                $clientIP = '8.8.8.8'; // Google DNS as fallback for local development
            }

            // Use ipinfo.io or similar service to detect country
            $response = Http::get("http://ipinfo.io/{$clientIP}/json");

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'country' => $data['country'] ?? null,
                    'country_code' => $data['country'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Country detection failed: ' . $e->getMessage());
        }

        return response()->json([
            'country' => null,
            'country_code' => null,
        ]);
    }

}
