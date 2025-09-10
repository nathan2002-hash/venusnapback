<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\Earning;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\VenusnapSystem;
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

            $pointsPerDiscovery = $venusnap->points_per_discovery; // e.g., 2 points
            $pointsPerDollar = $venusnap->points_per_dollar; // e.g., 1000 points per dollar

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

            // Also get reserve from ads if included (6 points per ad view)
            $adPoints = 0;
            if ($this->adsIncluded) {
                // Assuming 2 ads were shown, each giving 6 points to system
                $adPoints = 12; // 2 ads * 6 points each
                $systemReservePoints += $adPoints;
            }

            // Convert system reserve points to money and update Venusnap system
            $systemMoneyToAdd = $systemReservePoints / $pointsPerDollar;

            $venusnap->increment('system_money', $systemMoneyToAdd);
            $venusnap->increment('reserved_points', $systemReservePoints);
            $venusnap->increment('total_points_spent', $totalPointsDistributed);
            $venusnap->increment('total_points_earned', $systemReservePoints);

            // Log system earnings entry
            Earning::create([
                'album_id' => null,
                'user_id' => null,
                'batch_id' => $this->batchId,
                'earning' => $systemMoneyToAdd,
                'points' => $systemReservePoints,
                'type' => 'system_reserve',
                'status' => 'completed',
                'meta' => json_encode([
                    'non_monetized_posts' => $nonMonetizedPostsCount,
                    'points_from_non_monetized' => $nonMonetizedPostsCount * 2,
                    'ad_points' => $adPoints,
                    'ads_included' => $this->adsIncluded,
                    'points_per_dollar' => $pointsPerDollar,
                    'calculation' => "{$systemReservePoints} points / {$pointsPerDollar} = \${$systemMoneyToAdd}",
                    'total_posts_in_batch' => $this->totalPostsCount,
                    'monetized_posts' => $this->monetizedPostsCount,
                    'timestamp' => now()->toDateTimeString(),
                ]),
            ]);

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
                'ad_points_earned' => $adPoints,
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
