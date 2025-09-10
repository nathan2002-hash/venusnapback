<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\LinkAdShare;
use App\Models\VenusnapSystem;
use App\Models\Earning;
use App\Models\User;


class ProcessAdShareRewardJob implements ShouldQueue
{
    use Queueable;
    protected $shortCode;
    protected $viewerUserId;
    protected $ipAddress;
    protected $userAgent;

    public function __construct($shortCode, $viewerUserId = null, $ipAddress = null, $userAgent = null)
    {
        $this->shortCode = $shortCode;
        $this->viewerUserId = $viewerUserId;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }

    /**
     * Create a new job instance.
     */
    public function handle()
    {
        DB::transaction(function () {
            // Only check by user_id, not ip_address
            if ($this->viewerUserId) {
                $recentVisit = DB::table('link_ad_visits')
                    ->where('link_ad_share_id', function($query) {
                        $query->select('id')
                            ->from('link_ad_shares')
                            ->where('short_code', $this->shortCode);
                    })
                    ->where('user_id', $this->viewerUserId)
                    ->where('created_at', '>', now()->subHours(24))
                    ->exists();

                if ($recentVisit) {
                    Log::warning('Duplicate share link click detected for user', [
                        'short_code' => $this->shortCode,
                        'user_id' => $this->viewerUserId
                    ]);
                    return; // Exit early if duplicate click by same user
                }
            }

            // Record this visit in link_ad_visits
            $shareLink = LinkAdShare::where('short_code', $this->shortCode)->first();
            if ($shareLink) {
                DB::table('link_ad_visits')->insert([
                    'link_ad_share_id' => $shareLink->id,
                    'ip_address' => $this->ipAddress,
                    'user_id' => $this->viewerUserId,
                    'is_logged_in' => !is_null($this->viewerUserId),
                    'user_agent' => $this->userAgent,
                    'referrer' => request()->header('referer'),
                    'device_info' => request()->header('Device-Info'),
                    'country' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // If no user_id (guest user), give all 6 points to Venusnap and exit
            if (!$this->viewerUserId) {
                $venusnap = VenusnapSystem::first();
                if ($venusnap) {
                    $pointsToVenusnap = 6; // All 6 points to Venusnap for guest clicks
                    $pointsPerDollar = $venusnap->points_per_dollar;
                    $systemMoneyToAdd = $pointsToVenusnap / $pointsPerDollar;

                    // $venusnap->increment('system_money', $systemMoneyToAdd);
                    // $venusnap->increment('reserved_points', $pointsToVenusnap);
                    // $venusnap->increment('total_points_earned', $pointsToVenusnap);

                    Log::info("Guest click on share link {$this->shortCode}, Venusnap received all 6 points");
                }
                return; // Exit after processing guest click
            }

            // Continue with normal processing for logged-in users
             $shareLinkWithUser = LinkAdShare::with(['user.account', 'ad'])
            ->where('short_code', $this->shortCode)
            ->first();

            if (!$shareLinkWithUser) {
                Log::warning('Share link not found', ['short_code' => $this->shortCode]);
                return;
            }

            $sharingUser = $shareLinkWithUser->user;
            $ad = $shareLinkWithUser->ad;

            if (!$sharingUser) {
                Log::info('Sharing user not found', ['short_code' => $this->shortCode]);
                return;
            }

            // Get Venusnap system configuration
            $venusnap = VenusnapSystem::first();
            if (!$venusnap) {
                Log::error('Venusnap system not configured for ad share reward');
                return;
            }

            $pointsPerShare = 2; // 2 points for the sharer
            $pointsToVenusnap = 4; // 4 points to Venusnap system
            $totalPointsFromAd = 6; // Total points from ad click
            $pointsPerDollar = $venusnap->points_per_dollar;

            // Check if sharing user has any album with active monetization
            $monetizedAlbum = DB::table('albums')
                ->where('user_id', $sharingUser->id)
                ->where('monetization_status', 'active')
                ->first();

            $hasActiveMonetization = !is_null($monetizedAlbum);

            if ($hasActiveMonetization && $sharingUser->account) {
                // User has active monetization - add to account balance
                $amountToAdd = $pointsPerShare / $pointsPerDollar;
                $sharingUser->account->increment('available_balance', $amountToAdd);
                $sharingUser->account->increment('account_balance', $amountToAdd);

                $rewardType = 'account_balance';
                $rewardDescription = "Added to account balance";
            } else {
                // User doesn't have active monetization - add to user points
                $sharingUser->increment('points', $pointsPerShare);
                $amountToAdd = 0; // No money added to account

                $rewardType = 'user_points';
                $rewardDescription = "Added to user points";
            }

            // Always add 4 points to Venusnap system reserve
            $systemMoneyToAdd = $pointsToVenusnap / $pointsPerDollar;
            // $venusnap->increment('system_money', $systemMoneyToAdd);
            // $venusnap->increment('reserved_points', $pointsToVenusnap);
            // $venusnap->increment('total_points_earned', $pointsToVenusnap);

            // Only create earning entry if user has active monetization
            if ($hasActiveMonetization) {
                Earning::create([
                    'album_id' => $monetizedAlbum->id,
                    'user_id' => $sharingUser->id,
                    'batch_id' => 'ad_share_' . $this->shortCode,
                    'earning' => $amountToAdd,
                    'points' => $pointsPerShare,
                    'type' => 'ad_share_reward',
                    'status' => 'completed',
                    'meta' => json_encode([
                        'short_code' => $this->shortCode,
                        'ad_id' => $ad->id ?? null,
                        'points_per_share' => $pointsPerShare,
                        'points_to_venusnap' => $pointsToVenusnap,
                        'total_points_from_ad' => $totalPointsFromAd,
                        'points_per_dollar' => $pointsPerDollar,
                        'calculation' => "{$pointsPerShare} points to user / {$pointsToVenusnap} points to system",
                        'reward_type' => $rewardType,
                        'has_active_monetization' => $hasActiveMonetization,
                        'triggered_by_viewer' => $this->viewerUserId,
                        'shared_by_user' => $sharingUser->id,
                        'ip_address' => $this->ipAddress,
                        'user_agent' => $this->userAgent,
                        'timestamp' => now()->toDateTimeString(),
                    ]),
                ]);
            } else {
                // Just log that points were added to user points without creating earning record
                Log::info("Added {$pointsPerShare} points to user {$sharingUser->id} points balance (no monetization)");
            }

            Log::info("Processed ad share reward: {$rewardDescription} for user {$sharingUser->id}, Venusnap received {$pointsToVenusnap} points");
        });
    }
}
