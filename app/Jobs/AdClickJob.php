<?php

namespace App\Jobs;

use App\Models\Ad;
use App\Models\Adboard;
use App\Models\AdClick;
use App\Models\AdSession;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdClickJob implements ShouldQueue
{
    use Queueable;

    protected $ad_id;
    protected $ip_address;
    protected $device_info;
    protected $user_agent;
    protected $user_id;

    /**
     * Create a new job instance.
     */
    public function __construct($ad_id, $ip_address, $device_info, $user_agent, $user_id)
    {
        $this->ad_id = $ad_id;
        $this->ip_address = $ip_address;
        $this->device_info = $device_info;
        $this->user_agent = $user_agent;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $ad = Ad::find($this->ad_id);
        $adboard = Adboard::find($ad->adboard_id);
        if (!$adboard || $adboard->points <= 0) {
            return response()->json(['error' => 'Adboard not found or insufficient points'], 400);
        }
        $adboard->decrement('points', 1);

        //session
        $session = new AdSession();
        $session->ip_address = $this->ip_address;
        $session->user_id = $this->user_id;
        $session->device_info = $this->device_info;
        $session->user_agent = $this->user_agent;
        $session->save();

        //impressions
        $adclick = new AdClick();
        $adclick->ad_id = $this->ad_id;
        $adclick->user_id = $this->user_id;
        $adclick->ad_session_id = $session->id;
        $adclick->points_used = 1;
        $adclick->save();
    }
}
