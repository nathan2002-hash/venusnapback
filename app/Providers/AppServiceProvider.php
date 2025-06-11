<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Passport::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }
        Passport::loadKeysFrom(storage_path('oauth'));
        //Passport::loadKeysFrom(__DIR__.'/../secrets/oauth');

        // Register Passport routes
        //Passport::routes();

        // Optional: Set token expiration times
        Passport::tokensExpireIn(now()->addDays(15)); // Access tokens expire in 15 days
        Passport::refreshTokensExpireIn(now()->addDays(30)); // Refresh tokens expire in 30 days
    }
}
