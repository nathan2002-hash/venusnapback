<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

if (!function_exists('formatDateTimeForUser')) {
    function formatDateTimeForUser($dateTime, $timezone = null)
    {
        $timezone = $timezone ?? (Auth::user()->timezone ?? 'Africa/Lusaka');

        if (!$dateTime instanceof Carbon) {
            $dateTime = Carbon::parse($dateTime);
        }

        $dateTime = $dateTime->timezone($timezone);
        $now = now($timezone);

        if ($dateTime->isToday()) {
            return $dateTime->diffForHumans();
        } elseif ($dateTime->isYesterday()) {
            return 'Yesterday at ' . $dateTime->format('H:i');
        } elseif ($dateTime->diffInDays($now) <= 7) {
            return $dateTime->format('l \a\t H:i');
        } else {
            return $dateTime->format('d M Y, H:i');
        }
    }
}
