<?php

namespace App\Http\Controllers\Api;

use App\Models\AppMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AppMessageUserAction;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class AppStatusController extends Controller
{
    public function checkAppStatus(Request $request)
    {
        $currentVersion = $request->header('X-App-Version');
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;
        $platform = str_contains(strtolower($request->header('X-App-type')), 'android') ? 'android' : 'ios';

        // Check maintenance first
        if (env('APP_MAINTENANCE_MODE') == true) {
            // If maintenance mode is enabled, return maintenance response
            return $this->getMaintenanceResponse();
        }

        // Check version requirements
        if ($versionResponse = $this->checkVersionRequirements($platform, $currentVersion)) {
            return $versionResponse;
        }

        // Only check messages for authenticated users
        if ($request->user()) {
             $profileUrl = $request->user()->profile_compressed
            ? Storage::disk('s3')->url($request->user()->profile_compressed)
            : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($request->user()->email))) . '?s=100&d=mp';
            $responseData['user'] = [
            'username' => $request->user()->username,
            'profile' => $profileUrl,
            'fullname' => $request->user()->name,
            'email_verified' => $request->user()->hasVerifiedEmail(),
            'phone_verified' => $request->user()->phone_verified_at !== null,
        ];
            $message = AppMessage::query()
                ->active()
                ->where(function($q) use ($platform) {
                    $q->whereNull('platforms')
                    ->orWhereJsonContains('platforms', $platform);
                })
                ->whereDoesntHave('userActions', function($q) use ($request) {
                    $q->where('user_id', $request->user()->id)
                    ->whereIn('action', ['dismissed', 'clicked']); // Check for either dismissed or clicked
                })
                ->first();

            if ($message) {
                // Check if user has never seen this message before
                if (!AppMessageUserAction::where('app_message_id', $message->id)
                    ->where('user_id', $request->user()->id)
                    ->exists()) {

                    // Record initial 'viewed' action
                    AppMessageUserAction::create([
                        'app_message_id' => $message->id,
                        'user_id' => $request->user()->id,
                        'ip' => $ipaddress,
                        'action' => 'viewed', // Changed from 'dismissed' to 'viewed'
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

                // If user has seen but not dismissed/clicked, show message again
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

        return response()->json($responseData);

        //return response()->json(['status' => 'normal']);
    }

    public function trackMessageAction(Request $request)
    {
        $validated = $request->validate([
            'message_id' => 'required|exists:app_messages,id',
            'action' => 'required|in:clicked,dismissed'
        ]);

        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        AppMessageUserAction::create([
            'app_message_id' => $validated['message_id'],
            'user_id' => $request->user()?->id,
            'ip' => $ipaddress,
            'device_id' => $request->header('X-Device-ID'),
            'action' => $validated['action'],
            'app_version' => $request->header('X-App-Version'),
            'platform' => str_contains(strtolower($request->header('X-App-Type')), 'android') ? 'android' : 'ios'
        ]);

        return response()->json(['success' => true]);
    }

    protected function getMaintenanceResponse()
    {
        $messageId = env('UPDATE_SUGGESTED_MESSAGE_ID');
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
                'app_store_url' => "https://play.google.com/store/apps/details?id=com.enflick.android.TextNow",
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
            'android' => "https://play.google.com/apps/test/com.venusnap.app/7",
            'ios' => "https://play.google.com/store/apps/details?id=com.enflick.android.TextNow"
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

    public function getAlbumImages(Request $request, $album)
    {
        $perPage = 8;
        $page = $request->input('page', 1);

        $posts = Post::with(['postMedias.comments', 'postMedias.admires'])
            ->where('album_id', $album)
            ->where('status', 'active')
            ->get();

        $allImages = collect();

        foreach ($posts as $post) {
            foreach ($post->postMedias->sortBy('sequence_order') as $media) {
                $allImages->push([
                    'id' => $media->id,
                    'url' => Storage::disk('s3')->url($media->file_path_compress),
                    'post_id' => $post->id,
                    'post_description' => $post->description ?: 'No description provided by the creator',
                    'image_count' => $post->postMedias->count(),
                    'created_at' => $media->created_at,
                    'comments_count' => $media->comments->count(),
                    'likes_count' => $media->admires->count(),
                ]);
            }
        }

        // Create Laravel-style paginator manually
        $total = $allImages->count();
        $sliced = $allImages->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $sliced,
            $total,
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => $request->query()]
        );

        return response()->json($paginator);
    }

}
