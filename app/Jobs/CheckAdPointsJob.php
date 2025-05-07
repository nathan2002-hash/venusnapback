<?php

namespace App\Jobs;

use App\Models\Ad;
use App\Models\Adboard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckAdPointsJob implements ShouldQueue
{
    use Queueable;

    public $delay = 1800; // 30 minutes

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // Use transaction for atomic updates
            DB::transaction(function () {
                // Get ads with points <= 0 that aren't already marked as insufficient
                $ads = Ad::where('points', '<=', 0)
                    ->where('status', '!=', 'insufficient')
                    ->with('adboard') // Assuming there's a relationship to AdBoard
                    ->get();

                $count = $ads->count();

                if ($count > 0) {
                    // Collect all affected ad board IDs
                    $adBoardIds = $ads->pluck('adboard_id')->unique()->filter();

                    // Update all matching ads
                    Ad::whereIn('id', $ads->pluck('id'))
                        ->update(['status' => 'insufficient']);

                    // Update related ad boards if they exist
                    if ($adBoardIds->isNotEmpty()) {
                        Adboard::whereIn('id', $adBoardIds)
                            ->update(['status' => 'insufficient']);
                    }

                    Log::info("Marked $count ads and their ad boards as insufficient points");
                }
            });

            // Dispatch the next job
            self::dispatch()->delay(now()->addSeconds($this->delay));

        } catch (\Exception $e) {
            Log::error("Failed to check ad points: " . $e->getMessage());

            // Retry the job with exponential backoff
            $this->release(60); // Wait 1 minute before retry
        }
    }
}
