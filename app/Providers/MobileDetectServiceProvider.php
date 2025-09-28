<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Detection\MobileDetect;
use View;


class MobileDetectServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $browser = new MobileDetect();

        View::share('browser', $browser);
    }
}
