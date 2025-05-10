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
    $user = $request->user();
    $deviceId = $request->header('X-Device-ID'); // From Flutter app
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

    // Get active messages with tracking
    $message = AppMessage::active()
        ->where(function($q) use ($platform) {
            $q->whereNull('platforms')
              ->orWhereJsonContains('platforms', $platform);
        })
        ->whereDoesntHave('userActions', function($q) use ($user, $deviceId) {
            $q->where('user_id', $user?->id)
              ->orWhere('device_id', $deviceId);
        })
        ->first();

    if ($message) {
        // Record view action
        AppMessageUserAction::create([
            'app_message_id' => $message->id,
            'user_id' => $user?->id,
            'device_id' => $deviceId,
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
            'message_id' => $message->id // For tracking clicks
        ]);
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
        // Check for custom maintenance message in database
        $maintenanceMessage = AppMessage::where('type', 'maintenance')
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->first();

        if ($maintenanceMessage) {
            return response()->json([
                'status' => 'maintenance',
                'title' => $maintenanceMessage->title,
                'message' => $maintenanceMessage->content,
                'image' => asset($maintenanceMessage->image_path),
                'button_text' => $maintenanceMessage->button_text,
                'button_action' => $maintenanceMessage->button_action,
                'show_skip' => $maintenanceMessage->dismissible,
                'app_store_url' => null,
                'estimated_restore_time' => $maintenanceMessage->end_at->toIso8601String()
            ]);
        }

        // Default maintenance response
        return response()->json([
            'status' => 'maintenance',
            'title' => 'Maintenance in Progress',
            'message' => 'We are performing scheduled maintenance to improve your experience. Please check back later.',
            'image' => asset('images/system/maintenance.png'),
            'button_text' => null,
            'button_action' => null,
            'show_skip' => false,
            'app_store_url' => null,
            'estimated_restore_time' => null
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

            return response()->json([
                'status' => $isCritical ? 'update_required' : 'update_suggested',
                'title' => $isCritical ? 'Update Required' : 'New Version Available',
                'message' => $isCritical
                    ? 'This version is no longer supported. Please update to continue using the app.'
                    : 'A newer version with exciting features is available!',
                'image' => asset('images/system/update-'.($isCritical ? 'required' : 'available').'.png'),
                'button_text' => 'Update Now',
                'button_action' => 'update',
                'show_skip' => !$isCritical, // Only allow skip for non-critical updates
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
