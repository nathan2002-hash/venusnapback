<?php

namespace App\Providers;

use Laravel\Horizon\Horizon;
use Illuminate\Support\Facades\Gate;
use App\Notifications\SmsHorizonNotification;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

    //    Horizon::routeSmsNotificationsTo(function ($notifiable, $notification) {
    //         $job = class_basename($notification->job ?? 'Unknown');
    //         $message = "Venusnap Problem Detected: {$job} job failed.";

    //         (new \App\Notifications\SmsHorizonNotification())->send('260970333596', $message);
    //     });

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            return in_array(optional($user)->email, [
                'nathan@venusnap.com',
            ]);
        });
    }
}
