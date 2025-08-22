<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Earning;
use App\Models\Post;
use App\Models\InfluencerReferral;

class ReferralSignup implements ShouldQueue
{
    use Queueable;
    protected $postId;
    protected $userId;
    protected $ipAddress;
    protected $userAgent;
    protected $deviceInfo;
    protected $duration;
    protected $clicked;

    /**
     * Create a new job instance.
     */
    public function __construct($postId, $userId, $ipAddress, $userAgent, $deviceInfo, $duration = 0, $clicked = false)
    {
        $this->postId = $postId;
        $this->userId = $userId;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->deviceInfo = $deviceInfo;
        $this->duration = $duration;
        $this->clicked = $clicked;
    }

    /**
     * Execute the job.
     */
    public function handle()
{
    $post = Post::with('album', 'user.account', 'user.influencer')->find($this->postId);

    if (!$post) return;

    $user = $post->user; // Post owner

    // ✅ Only continue if the post owner is an influencer
    if (!$user || !$user->account || !$user->influencer) {
        return; // Not an influencer, no reward
    }

    // Check if already rewarded (avoid double-crediting)
    $already = InfluencerReferral::where('influencer_id', $user->id)
        ->where('referred_user_id', $this->userId)
        ->where('post_id', $post->id)
        ->exists();

    if ($already) return;

    $reward = 0.20; // e.g., $0.20 per click

    // Create referral record
    InfluencerReferral::create([
        'influencer_id'    => $user->id,
        'referred_user_id' => $this->userId,
        'post_id'          => $post->id,
        'reward'           => $reward,
        'milestone_type'   => 'post_click',
        'milestone_value'  => 1,
        'credited'         => true,
    ]);

    // Credit influencer’s account balances
    $user->account->increment('available_balance', $reward);
    $user->account->increment('account_balance', $reward);

    // Credit influencer’s monetization balance
    $user->influencer->increment('monetization_balance', $reward);

    // Log in Earning
    Earning::create([
        'album_id' => $post->album_id ?? null,
        'batch_id' => null,
        'earning' => $reward,
        'points' => 0,
        'type' => 'influencer_click',
        'meta' => json_encode([
            'post_id' => $post->id,
            'influencer_id' => $user->id,
            'referred_user_id' => $this->userId,
            'ip' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'device_info' => $this->deviceInfo,
            'timestamp' => now()->toDateTimeString(),
        ]),
    ]);
}

}
