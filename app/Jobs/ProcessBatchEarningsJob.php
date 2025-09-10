<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\Earning;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\VenusnapSystem;
use App\Models\Adboard;
use App\Models\Ad;
use Illuminate\Support\Facades\DB;

class ProcessBatchEarningsJob implements ShouldQueue
{
    use Queueable;
    protected $batchId;
    protected $fetchedPosts;
    protected $monetizedPosts;
    protected $adsIncluded;
    protected $totalPostsCount;
    protected $monetizedPostsCount;
    protected $adIds;
    protected $adboardIds;
    protected $user_id;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->batchId = $data['batch_id'];
        $this->fetchedPosts = $data['fetched_posts'] ?? [];
        $this->monetizedPosts = $data['monetized_posts'] ?? [];
        $this->adsIncluded = $data['ads_included'] ?? false;
        $this->totalPostsCount = $data['total_posts_count'] ?? 0;
        $this->monetizedPostsCount = $data['monetized_posts_count'] ?? 0;
        $this->adIds = $data['ad_ids'] ?? [];
        $this->adboardIds = $data['adboard_ids'] ?? [];
        $this->user_id = $data['user_id'] ?? null; // Get user_id from data array
    }

    /**
     * Execute the job.
     */
   public function handle()
{
    DB::transaction(function () {
        // Get Venusnap system configuration
        $venusnap = VenusnapSystem::first();
        if (!$venusnap) {
            Log::error('Venusnap system not configured for batch: ' . $this->batchId);
            return;
        }

        $pointsPerDiscovery = $venusnap->points_per_discovery;
        $pointsPerDollar = $venusnap->points_per_dollar;

        // Step 0: Decrement adboard points for ads that were shown (with abuse prevention)
        $totalAdPointsDeducted = 0;
        $validAdPoints = 0;

        if ($this->adsIncluded && !empty($this->adIds) && $this->user_id) {
            foreach ($this->adIds as $adId) {
                $ad = Ad::find($adId);
                if (!$ad) continue;

                // Check if user has seen THIS SPECIFIC AD in the last 30 minutes
                $recentAdView = DB::table('ad_impressions')
                    ->where('ad_id', $adId)
                    ->where('user_id', $this->user_id)
                    ->where('created_at', '>', now()->subMinutes(30))
                    ->exists();

                if ($recentAdView) {
                    Log::warning('User has seen this ad recently, skipping', [
                        'user_id' => $this->user_id,
                        'ad_id' => $adId
                    ]);
                    continue; // Skip only this specific ad
                }

                $adboard = Adboard::find($ad->adboard_id);
                if (!$adboard || $adboard->points <= 0) {
                    Log::warning("Adboard not found or insufficient points for ad: {$adId}");
                    continue;
                }

                $pointsToDeduct = 3; // 3 points per ad view

                if ($adboard->points >= $pointsToDeduct) {
                    $adboard->decrement('points', $pointsToDeduct);
                    $totalAdPointsDeducted += $pointsToDeduct;
                    $validAdPoints += $pointsToDeduct;
                    Log::info("Deducted {$pointsToDeduct} points from adboard {$ad->adboard_id} for ad {$adId}");

                    // Record the ad impression to prevent future abuse
                    DB::table('ad_impressions')->insert([
                        'ad_id' => $adId,
                        'user_id' => $this->user_id,
                        'points_used' => $pointsToDeduct,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    Log::warning("Insufficient points in adboard {$ad->adboard_id} for ad {$adId}");
                }
            }
        }

        // Step 1: Process monetized posts - reward creators
        $monetizedPosts = Post::with('album.user.account')
            ->whereIn('id', $this->monetizedPosts)
            ->get();

        $groupedByAlbum = $monetizedPosts->groupBy('album_id');
        $totalPointsDistributed = 0;

        foreach ($groupedByAlbum as $albumId => $posts) {
            $album = $posts->first()->album;
            $user = $album->user ?? null;

            if (!$user || !$user->account) {
                Log::warning('User or account not found for album: ' . $albumId);
                continue;
            }

            // Calculate points: 1 point per post from this album
            $pointsEarned = $posts->count();
            $totalPointsDistributed += $pointsEarned;

            // Convert points to money (points / points_per_dollar)
            $amountToAdd = $pointsEarned / $pointsPerDollar;

            // Update user balance
            $user->account->increment('available_balance', $amountToAdd);
            $user->account->increment('account_balance', $amountToAdd);

            // Log earning entry
            Earning::create([
                'album_id' => $albumId,
                'user_id' => $user->id,
                'batch_id' => $this->batchId,
                'earning' => $amountToAdd,
                'points' => $pointsEarned,
                'type' => 'discovery_reward',
                'status' => 'completed',
                'meta' => json_encode([
                    'post_ids' => $posts->pluck('id')->toArray(),
                    'points_per_discovery' => $pointsPerDiscovery,
                    'points_per_dollar' => $pointsPerDollar,
                    'calculation' => "{$pointsEarned} points / {$pointsPerDollar} = \${$amountToAdd}",
                    'timestamp' => now()->toDateTimeString(),
                ]),
            ]);

            Log::info("Rewarded user {$user->id} with {$pointsEarned} points (\${$amountToAdd}) for album {$albumId}");
        }

        // Step 2: Calculate system reserve points from non-monetized content
        $nonMonetizedPostsCount = $this->totalPostsCount - $this->monetizedPostsCount;

        // System gets 2 points for each non-monetized post
        $systemReservePoints = $nonMonetizedPostsCount * 2;

        // Only add points from non-abusive ad views
        $systemReservePoints += $validAdPoints;

        // Convert system reserve points to money and update Venusnap system
        $systemMoneyToAdd = $systemReservePoints / $pointsPerDollar;

        $venusnap->increment('system_money', $systemMoneyToAdd);
        $venusnap->increment('reserved_points', $systemReservePoints);
        $venusnap->increment('total_points_spent', $totalPointsDistributed);
        $venusnap->increment('total_points_earned', $systemReservePoints);

        // Log batch processing
        Log::info("Processed earnings batch", [
            'batch_id' => $this->batchId,
            'total_posts' => $this->totalPostsCount,
            'monetized_posts' => $this->monetizedPostsCount,
            'non_monetized_posts' => $nonMonetizedPostsCount,
            'albums_rewarded' => $groupedByAlbum->count(),
            'total_points_distributed' => $totalPointsDistributed,
            'system_reserve_points' => $systemReservePoints,
            'system_money_added' => $systemMoneyToAdd,
            'ads_included' => $this->adsIncluded,
            'total_ad_points_deducted' => $totalAdPointsDeducted,
            'valid_ad_points_used' => $validAdPoints,
            'skipped_ads_due_to_abuse' => $totalAdPointsDeducted - $validAdPoints,
        ]);
    });
}

    public function failed(\Throwable $exception)
    {
        Log::error('ProcessBatchEarningsJob failed: ' . $exception->getMessage(), [
            'batch_id' => $this->batchId,
            'exception' => $exception->getTraceAsString()
        ]);
    }

}
