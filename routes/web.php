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

    //user routes
    Route::get('/users', 'Admin\UserController@index');
    Route::get('/user/{id}', 'Admin\UserController@show');

    //category routes
    Route::get('/categories', 'Admin\CategoryController@index');
    Route::post('/category/store', 'Admin\CategoryController@store');
    Route::get('/category/{id}', 'Admin\CategoryController@show');
});
