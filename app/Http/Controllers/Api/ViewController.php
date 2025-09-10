<?php

namespace App\Http\Controllers\Api;

use App\Models\View;
use App\Models\Saved;
use App\Models\Admire;
use App\Models\Report;
use App\Models\LinkShare;
use App\Models\PostMedia;
use App\Jobs\TrackViewJob;
use Illuminate\Http\Request;
use App\Jobs\TrackPostViewJob;
use App\Models\Recommendation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ReferralSignup;
use App\Models\LinkVisit;
use App\Models\Post;

class ViewController extends Controller
{

    public function view(Request $request)
    {
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        TrackViewJob::dispatch(
            Auth::id(),
            $realIp,
            $request->input('post_media_id'),
            $request->input('duration'),
            $request->header('User-Agent')
        );

        return response()->json(['message' => 'Job dispatched for tracking view']);
    }

    public function viewpost(Request $request)
    {
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        TrackPostViewJob::dispatch(
            Auth::id(),
            $realIp,
            $request->input('post_id'),
            $request->input('post_media_id'),
            $request->input('duration'),
            $request->header('User-Agent')
        );

        return response()->json(['message' => 'Job dispatched for tracking post view']);
    }

    public function viewg(Request $request)
    {
        // Track the view duration
        $view = new View();
        $view->user_id = Auth::user()->id;
        $view->ip_address = $request->ip();
        $view->post_media_id = $request->input('post_media_id');
        $view->duration = $request->input('duration');
        $view->user_agent = $request->header('User-Agent');
        $view->save();

        // Update the recommendation status to "seen"
        $postMediaId = $request->input('post_media_id');
        $userId = Auth::user()->id;

        $postmedia = PostMedia::find($postMediaId);
        if (!$postmedia) {
            return response()->json(['message' => 'Post media not found'], 404);
        }

        $postId = $postmedia->post_id;

        // ✅ Update recommendations with 'active' or 'fetched' status to 'seen'
        $updated = Recommendation::where('user_id', $userId)
            ->where('post_id', $postId)
            ->whereIn('status', ['active', 'fetched']) // Include both statuses
            ->update(['status' => 'seen']);

        return response()->json([
            'message' => 'View duration tracked and recommendation marked as seen',
            'updated_records' => $updated
        ]);
    }

    public function more(Request $request)
    {
        $mediaId = $request->query('media_id');
        $userId = Auth::id();

        $postmedia = PostMedia::find($mediaId);
        if (!$postmedia) {
            return response()->json(['error' => 'Post media not found'], 404);
        }

        $post_id = $postmedia->post_id;

        $isAdmired = Admire::where('post_media_id', $mediaId)
            ->where('user_id', $userId)
            ->exists();

        $isSaved = Saved::where('post_id', $post_id)
            ->where('user_id', $userId)
            ->exists();

        $isReported = Report::where('resource_id', $mediaId)
            ->where('target', 'post_media')
            ->where('user_id', $userId)
            ->exists();

        // Generate share URL with tracking
        $shareUrl = $this->generateTrackableLink($post_id, $mediaId, $userId);

        return response()->json([
            'isAdmired' => $isAdmired,
            'isSaved' => $isSaved,
            'isReported' => $isReported,
            'url' => $shareUrl,
        ]);
    }

    protected function generateTrackableLink($postId, $mediaId, $userId)
    {
        $share = LinkShare::create([
            'user_id' => $userId,
            'post_id' => $postId,
            'post_media_id' => $mediaId,
            'share_method' => 'direct', // Default, can be updated when shared via specific platform
            'share_url' => "https://www.venusnap.com/post/$postId/media/$mediaId",
        ]);

        // Optionally generate a short URL
        $shortCode = $this->generateShortCode();
        $share->update(['short_code' => $shortCode]);

        return "https://www.venusnap.com/post/$postId/media/$mediaId?ref=$shortCode";
    }

    protected function generateShortCode()
    {
        // Implement your short code generation logic
        return substr(md5(uniqid()), 0, 8);
    }

    public function sharePost($postId)
    {
        $post = Post::with('album')->find($postId);

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        // Check if user has access to this post
        if (Auth::id() !== $post->album->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get the first media of the post
        $postMedia = PostMedia::where('post_id', $postId)->orderBy('id')->first();
        $mediaId = $postMedia ? $postMedia->id : null;

        // Generate or get existing short code
        $shortCode = $this->generateShortCode($post);

        $share = LinkShare::create([
            'user_id' => Auth::user()->id,
            'post_id' => $postId,
            'post_media_id' => $mediaId,
            'share_method' => 'explore', // Default, can be updated when shared via specific platform
            'share_url' => "https://www.venusnap.com/explore/$postId",
        ]);

        // Create the full URL
        $shareUrl = "https://www.venusnap.com/explore/{$post->id}/?ref={$shortCode}";

        // Build the complete formatted message
        $shareMessage = $this->buildShareMessage($post, $shareUrl);
        $shareSubject = $post->title ?? 'Venusnap Post';

        return response()->json([
            'share_message' => $shareMessage,
            'share_subject' => $shareSubject,
            'share_url' => $shareUrl,
            'short_code' => $shortCode,
        ]);
    }

    private function buildShareMessage($post, $shareUrl)
    {
        $title = $post->title ?? 'Amazing Content';
        $description = $post->description ?? 'Check out this incredible content on Venusnap!';

        // Build the complete formatted message with URL
        $message = "🌟 {$title} 🌟\n\n";
        $message .= "{$description}\n\n";
        $message .= "👉 {$shareUrl}\n\n";
        $message .= "#Venusnap #ContentSharing";

        return $message;
    }

    public function trackVisit(Request $request)
    {
        $request->validate([
            'short_code' => 'string',
            'is_logged_in' => 'sometimes|boolean',
        ]);

        $share = LinkShare::where('short_code', $request->short_code)->first();

        if (!$share) {
            return response()->json(['error' => 'Invalid short code'], 404);
        }

        if (Auth::check()) {
            $userId = Auth::user()->id;
        } else {
            $userId = null;
        }

        $userAgent = $request->header('User-Agent');
        $deviceinfo = $request->header('Device-Info');
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();


        $visit = $share->visits()->create([
            'ip_address' => $realIp,
            'user_agent' => $userAgent,
            'device_info' => $deviceinfo,
            'referrer' => $request->short_code,
            'user_id' => $userId,
            'link_share_id' => $share->id,
            'is_logged_in' => $request->input('is_logged_in', false),
        ]);

        $userId = null;
        if (Auth::check()) {
            $userId = Auth::id();
            ReferralSignup::dispatch(
                $request->resource_id,
                $userId,
                $request->header('cf-connecting-ip') ?? $request->ip(),
                $request->header('User-Agent'),
                $request->header('Device-Info'),
                6, // Initial duration
                true // clicked = true
            );
        }
        return response()->json([
            'success' => true,
            'visit_id' => $visit->id,
        ]);
    }

    public function trackExploreVisit(Request $request)
    {
        $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'short_code' => 'nullable|string',
            'is_logged_in' => 'sometimes|boolean',
        ]);

        $share = LinkShare::where('short_code', $request->short_code)->first();

        if (!$share) {
            return response()->json(['error' => 'Invalid short code'], 404);
        }

        if (Auth::check()) {
            $userId = Auth::id();
        } else {
            $userId = null;
        }

        $userAgent = $request->header('User-Agent');
        $deviceInfo = $request->header('Device-Info');
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();

        // Create the explore visit record
        $exploreVisit = LinkVisit::create([
            'link_share_id' => $share->id,
            'ip_address' => $realIp,
            'user_id' => $userId,
            'is_logged_in' => $request->input('is_logged_in', false),
            'user_agent' => $userAgent,
            'referrer' => $request->short_code,
            'device_info' => $deviceInfo,
            'duration' => 5, // Fixed duration of 5 as requested
        ]);

        // If user is logged in, dispatch referral processing
        if (Auth::check()) {
            ReferralSignup::dispatch(
                $request->post_id, // Use post_id as resource_id
                $userId,
                $realIp,
                $userAgent,
                $deviceInfo,
                5, // Duration of 5 as requested
                true // clicked = true
            );
        }

        return response()->json([
            'success' => true,
            'visit_id' => $exploreVisit->id,
        ]);
    }
}
