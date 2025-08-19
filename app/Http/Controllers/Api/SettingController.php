<?php

namespace App\Http\Controllers\Api;

use App\Models\UserSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function getUserSettings()
    {
        $user = Auth::user();
        $settings = UserSetting::firstOrCreate(['user_id' => $user->id], [
            'sms_alert' => 1,
            'email_notifications' => 1,
            'tfa' => 0,
            'push_notifications' => 1,
            'dark_mode' => 0,
            'history' => 1
        ]);

        return response()->json([
            'sms_alert' => (bool) $settings->sms_alert,
            'email_notifications' => (bool) $settings->email_notifications,
            'tfa' => (bool) $settings->tfa,
            'push_notifications' => (bool) $settings->push_notifications,
            'dark_mode' => (bool) $settings->dark_mode,
            'history' => (bool) $settings->history
        ]);
    }

    public function updateUserSetting(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'setting_key' => 'required|string|in:sms_alert,email_notifications,tfa,push_notifications,dark_mode,history',
            'setting_value' => 'required|boolean',
        ]);

        $settings = UserSetting::firstOrCreate(['user_id' => $user->id]);
        $settings->update([$validated['setting_key'] => $validated['setting_value']]);

        return response()->json([
            'message' => 'Setting updated successfully',
            'updated_setting' => $validated['setting_key'],
            'new_value' => $validated['setting_value']
        ]);
    }

    public function getMonetizationStatus()
    {
        $user = Auth::user();

        // You could also check if the user has even applied before:
        // if (!$user->account->monetization_status) {
        //     return response()->json([
        //         'status' => 'not_applied',
        //         'message' => 'You have not applied for monetization yet.'
        //     ]);
        // }

        if ($user->account->monetization_status !== 'active') {
            return response()->json([
                'status' => $user->account->monetization_status ?? 'pending', // could be 'pending' or 'rejected'
                'message' => 'Your account is under review. We will notify you.'
            ]);
        }

        // If active
        return response()->json([
            'status' => 'approved',
            'message' => 'Your account is monetized. You are eligible to earn.'
        ]);
    }

}
