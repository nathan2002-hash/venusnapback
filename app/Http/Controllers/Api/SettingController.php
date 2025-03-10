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
            'sms_alert' => 0,
            'email_notifications' => 1,
            'tfa' => 0,
            'push_notifications' => 1,
            'dark_mode' => 0
        ]);

        return response()->json([
            'sms_alert' => (bool) $settings->sms_alert,
            'email_notifications' => (bool) $settings->email_notifications,
            'tfa' => (bool) $settings->tfa,
            'push_notifications' => (bool) $settings->push_notifications,
            'dark_mode' => (bool) $settings->dark_mode
        ]);
    }

    public function updateUserSetting(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'setting_key' => 'required|string|in:sms_alert,email_notifications,tfa,push_notifications,dark_mode',
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
}
