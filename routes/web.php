<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('user.welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/test-backblaze-connection', 'Api\PostController@testConnection');




Route::prefix('restricted')->middleware('auth', 'admin')->group(function () {
    Route::get('/welcome', 'Admin\WelcomeController@index');
});
