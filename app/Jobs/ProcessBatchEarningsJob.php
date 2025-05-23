<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\Earning;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessBatchEarningsJob implements ShouldQueue
{
    use Queueable;
    protected $batchId;
    protected $fetchedPosts;
    protected $adsIncluded;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->batchId = $data['batch_id'];
        $this->fetchedPosts = $data['fetched_posts'] ?? [];
        $this->adsIncluded = $data['ads_included'] ?? false;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Step 1: Get monetized post IDs with eligible albums
        $monetizedPosts = Post::with('album')
            ->whereIn('id', $this->fetchedPosts)
            ->get()
            ->filter(function ($post) {
                $album = $post->album;
                return $album && $album->is_verified && $album->monetization_status === 'active';
            });

        $groupedByAlbum = $monetizedPosts->groupBy('album_id');

        // Step 2: Create earnings per album
        foreach ($groupedByAlbum as $albumId => $posts) {
            // If ads were included, calculate points for monetized posts
            if ($this->adsIncluded) {
                $pointsEarned = $posts->count(); // 1 point per post (adjustable)
            } else {
                $pointsEarned = 0; // No points if ads weren't included
            }

            $album = $posts->first()->album;
            $user = $album->user ?? null;

            $conversionRate = 0.01; // 1 point = $0.01
            $amountToAdd = $pointsEarned * $conversionRate;

            if ($user && $user->account && $amountToAdd > 0) {
                $user->account->increment('available_balance', $amountToAdd);
            }

            // Step 4: Log earning entry with points or 0 if no points earned
            Earning::create([
                'album_id' => $albumId,
                'batch_id' => $this->batchId,
                'earning' => $amountToAdd,
                'points' => $pointsEarned,
                'type' => 'ad_revenue',
                'meta' => json_encode([
                    'fetched_post_ids' => $this->fetchedPosts,
                    'monetized_post_ids' => $posts->pluck('id')->toArray(),
                    'ads_included' => $this->adsIncluded,
                    'timestamp' => now()->toDateTimeString(),
                ]),
            ]);
        }

        // Optional: Log batch
        Log::info("Processed earnings batch", [
            'batch_id' => $this->batchId,
            'albums_rewarded' => $groupedByAlbum->count(),
            'ads_included' => $this->adsIncluded,
        ]);
    }

}
