<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\LinkAdShare;
use App\Models\LinkAdVisit;

class MoreAdController extends Controller
{
    // app/Http/Controllers/AdController.php
    public function generateShareUrl(Request $request, $adId)
    {
        $user = $request->user();

        // Validate the user has permission to share this ad
        $ad = Ad::findOrFail($adId);

        // Generate a unique short code
        $shortCode = $this->generateShortCode();
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();

        // Create the share record
        $share = LinkAdShare::create([
            'user_id' => $user->id,
            'ad_id' => $adId,
            'device_info' => $request->header('Device-Info'),
            'ip_address' => $realIp,
            'share_method' => 'direct', // Default, can be updated when shared via specific platform
            'share_url' => "https://www.venusnap.com/sponsored/{$shortCode}",
            'short_code' => $shortCode
        ]);

        $shareMessage = "Check out \"{$ad->adboard->name}\" on Venusnap: https://www.venusnap.com/sponsored/{$shortCode}";
        $shareSubject = $ad->adboard->name;

        // Return the trackable URL
        return response()->json([
            'share_url' => "https://www.venusnap.com/sponsored/{$shortCode}",
            'short_code' => $shortCode,
            'share_message' => $shareMessage,
            'share_subject' => $shareSubject
        ]);
    }

    protected function generateShortCode()
    {
        $length = 10;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        // Ensure the code is unique
        while (LinkAdShare::where('short_code', $randomString)->exists()) {
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
        }

        return $randomString;
    }

    public function resolveShortCode($shortCode)
    {
        // Find the share link with active ad
        $shareLink = LinkAdShare::where('short_code', $shortCode)->first();

        if (!$shareLink || !$shareLink->ad) {
            return response()->json([
                'error' => 'Ad not found or expired',
                'status' => 404
            ], 404);
        }

        // Return the ad data
        return response()->json([
            'ad_id' => $shareLink->ad_id,
        ]);
    }
}
