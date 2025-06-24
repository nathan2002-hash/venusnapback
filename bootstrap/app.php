<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckAccountStatus;
use App\Http\Middleware\BlockMultiple;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
            channels: __DIR__.'/../routes/channels.php',
        using: function () {
            // Route::middleware('api')
            //     ->prefix('api')
            //     ->group(base_path('routes/api.php'));

            Route::prefix('api')
                ->middleware('api')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/api.php'));
            Route::prefix('/')
                ->middleware('web')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\BlockMultiple::class);
        $middleware->statefulApi();
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'check.account.status' => CheckAccountStatus::class,
            'throttle.404' => BlockMultiple::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
