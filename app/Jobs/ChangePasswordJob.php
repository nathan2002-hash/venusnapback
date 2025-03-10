<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChangePasswordJob implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $currentPassword;
    protected $newPassword;
    protected $userAgent;
    protected $ipaddress;
    protected $deviceinfo;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $currentPassword, $newPassword, $userAgent, $deviceinfo, $ipaddress)
    {
        $this->user = $user;
        $this->currentPassword = $currentPassword;
        $this->newPassword = $newPassword;
        $this->userAgent = $userAgent;
        $this->ipaddress = $ipaddress;
        $this->deviceinfo = $deviceinfo;
    }


    /**
     * Execute the job.
     */
    public function handle()
    {
        // Check if the current password matches
        if (Hash::check($this->currentPassword, $this->user->password)) {
            // Update password if current password is correct
            $this->user->update([
                'password' => Hash::make($this->newPassword),
            ]);

            // Log success activity
            Activity::create([
                'title' => 'Password Updated',
                'description' => 'Your account password was successfully changed.',
                'source' => 'Authentication',
                'user_id' => $this->user->id,
                'status' => true, // Success status
                'user_agent' => $this->userAgent,
                'ipaddress' => $this->ipaddress,
                'device_info' => $this->deviceinfo,
            ]);
        } else {
            // Log failed activity if the current password is incorrect
            Activity::create([
                'title' => 'Password Update Failed',
                'description' => 'Password change failed due to incorrect current password.',
                'source' => 'Authentication',
                'user_id' => $this->user->id,
                'status' => false, // Failed status
                'user_agent' => $this->userAgent,
                'ipaddress' => $this->ipaddress,
                'device_info' => $this->deviceinfo,
            ]);
        }
    }
}
