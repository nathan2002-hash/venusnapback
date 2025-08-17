<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

if (!function_exists('formatDateTimeForUser')) {
    function formatDateTimeForUser($dateTime, $timezone = null)
    {
        $timezone = $timezone ?? (Auth::user()->timezone ?? 'Africa/Lusaka');

        // Ensure $dateTime is Carbon
        if (!$dateTime instanceof Carbon) {
            $dateTime = Carbon::parse($dateTime);
        }

        $dateTime = $dateTime->timezone($timezone);
        $now = now($timezone);

        if ($dateTime->isToday()) {
            return $dateTime->diffForHumans(); // "2 hours ago"
        } elseif ($dateTime->isYesterday()) {
            return 'Yesterday at ' . $dateTime->format('H:i');
        } elseif ($dateTime->diffInDays($now) <= 7) {
            return $dateTime->format('l \a\t H:i'); // "Monday at 14:30"
        } else {
            return $dateTime->format('d M Y, H:i'); // "15 Jun 2023, 14:30"
        }
    }
}
