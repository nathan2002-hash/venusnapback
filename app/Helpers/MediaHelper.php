<?php

use Illuminate\Support\Facades\Crypt;

if (!function_exists('generateSecureMediaUrl')) {
    function generateSecureMediaUrl($filePath)
    {
        $expiresAt = now()->addMinutes(5)->timestamp;

        $payload = json_encode([
            'file' => $filePath,
            'expires' => $expiresAt,
        ]);

        $token = Crypt::encryptString($payload);

        return "https://media.venusnap.com/file?token=" . urlencode($token);
    }
}
