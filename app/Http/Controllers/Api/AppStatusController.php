<?php

namespace App\Http\Controllers\Api;

use App\Models\AppMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AppMessageUserAction;

class AppStatusController extends Controller
{
    public function checkAppStatus(Request $request)
    {
        $currentVersion = $request->header('X-App-Version');
        $platform = str_contains(strtolower($request->header('User-Agent')), 'android') ? 'android' : 'ios';

        // Check maintenance first
        if (config('app.maintenance_mode')) {
            return $this->getMaintenanceResponse();
        }

        // Check version requirements
        if ($versionResponse = $this->checkVersionRequirements($platform, $currentVersion)) {
            return $versionResponse;
        }

        // Only check messages for authenticated users
        if ($request->user()) {
            $message = AppMessage::query()
                ->active() // Using the scope here
                ->where(function($q) use ($platform) {
                    $q->whereNull('platforms')
                    ->orWhereJsonContains('platforms', $platform);
                })
                ->whereDoesntHave('userActions', function($q) use ($request) {
                    $q->where('user_id', $request->user()->id);
                })
                ->first();

            if ($message) {
                AppMessageUserAction::create([
                    'app_message_id' => $message->id,
                    'user_id' => $request->user()->id,
                    'action' => 'viewed',
                    'app_version' => $currentVersion,
                    'platform' => $platform
                ]);

                return response()->json([
                    'status' => $message->type,
                    'title' => $message->title,
                    'message' => $message->content,
                    'image' => asset($message->image_path),
                    'button_text' => $message->button_text,
                    'button_action' => $message->button_action,
                    'show_skip' => $message->dismissible,
                    'app_store_url' => $this->getAppStoreUrl($platform),
                    'message_id' => $message->id
                ]);
            }
        }

        return response()->json(['status' => 'normal']);
    }

    public function trackMessageAction(Request $request)
    {
        $validated = $request->validate([
            'message_id' => 'required|exists:app_messages,id',
            'action' => 'required|in:clicked,dismissed'
        ]);

        AppMessageUserAction::create([
            'app_message_id' => $validated['message_id'],
            'user_id' => $request->user()?->id,
            'device_id' => $request->header('X-Device-ID'),
            'action' => $validated['action'],
            'app_version' => $request->header('X-App-Version'),
            'platform' => str_contains(strtolower($request->header('User-Agent')), 'android') ? 'android' : 'ios'
        ]);

        return response()->json(['success' => true]);
    }

   protected function getMaintenanceResponse()
    {
        $messageId = env('MAINTENANCE_MESSAGE_ID');
        $message = AppMessage::find($messageId);

        if ($message) {
            return response()->json([
                'message_id' => $messageId,
                'status' => 'maintenance',
                'title' => $message->title,
                'message' => $message->content,
                'image' => asset($message->image_path),
                'button_text' => $message->button_text,
                'button_action' => $message->button_action,
                'show_skip' => $message->dismissible,
                'app_store_url' => null,
                'estimated_restore_time' => $message->end_at?->toIso8601String()
            ]);
        }

        // Fallback if no message configured
        return response()->json([
            'status' => 'maintenance',
            'title' => 'Maintenance in Progress',
            'message' => 'We are performing scheduled maintenance. Please check back later.',
            'image' => null,
            'button_text' => null,
            'button_action' => null,
            'show_skip' => false,
            'app_store_url' => null
        ]);
    }

    protected function checkVersionRequirements($platform, $currentVersion)
{
    $minVersions = [
        'android' => env('MIN_ANDROID_VERSION', '2.0'),
        'ios' => env('MIN_IOS_VERSION', '2.0')
    ];

    $forceUpdateVersions = json_decode(env('FORCE_UPDATE_VERSIONS', '[]'), true);

    // Check if current version is below minimum
    if (version_compare($currentVersion, $minVersions[$platform], '<')) {
        $isCritical = in_array($currentVersion, $forceUpdateVersions[$platform] ?? []);
        $messageType = $isCritical ? 'update_required' : 'update_suggested';
        $messageId = env(strtoupper($messageType).'_MESSAGE_ID');

        $message = AppMessage::find($messageId);

        if ($message) {
            return response()->json([
                'status' => $messageType,
                'title' => $message->title,
                'message' => $message->content,
                'image' => asset($message->image_path),
                'button_text' => $message->button_text,
                'button_action' => $message->button_action,
                'show_skip' => !$isCritical,
                'app_store_url' => $this->getAppStoreUrl($platform),
                'current_version' => $currentVersion,
                'min_version' => $minVersions[$platform],
                'message_id' => $messageId,
            ]);
        }

        // Fallback if no message configured
        return response()->json([
            'status' => $messageType,
            'title' => $isCritical ? 'Update Required' : 'New Version Available',
            'message' => $isCritical
                ? 'This version is no longer supported. Please update to continue using the app.'
                : 'A newer version with exciting features is available!',
            'image' => 'https://venusnaplondon.s3.eu-west-2.amazonaws.com/system/update.jpg',
            'button_text' => 'Update Now',
            'button_action' => 'update',
            'show_skip' => !$isCritical,
            'app_store_url' => $this->getAppStoreUrl($platform),
            'current_version' => $currentVersion,
            'min_version' => $minVersions[$platform]
        ]);
    }

    return null;
}

    protected function getAppStoreUrl($platform, $countryCode = null)
    {
        $baseUrls = [
            'android' => config('app.android_store_url'),
            'ios' => config('app.ios_store_url')
        ];

        $url = $baseUrls[$platform] ?? $baseUrls['android'];

        // Add country code if provided (for localized store links)
        if ($countryCode) {
            $url .= parse_url($url, PHP_URL_QUERY) ? '&' : '?';
            $url .= 'gl='.$countryCode;
        }

        // Add campaign tracking for analytics
        $url .= parse_url($url, PHP_URL_QUERY) ? '&' : '?';
        $url .= 'utm_source=app&utm_medium=version_check&utm_campaign=update_prompt';

        return $url;
    }
}
