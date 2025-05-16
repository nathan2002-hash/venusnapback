<?php

use App\Jobs\CheckAdPointsJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('user.welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/restricted/welcome', function () {
        return view('dashboard');
    })->name('dashrd');
});

Route::get('/test-backblaze-connection', 'Api\PostController@testConnection');

Route::prefix('restricted')->middleware('auth', 'admin')->group(function () {
    Route::get('/welcome', 'Admin\WelcomeController@index')->name('dashboard');

    //user routes
    Route::get('/users', 'Admin\UserController@index');
    Route::get('/user/{id}', 'Admin\UserController@show');

    //posts routes
    Route::get('/posts', 'Admin\PostController@index');
    Route::get('/post/{id}', 'Admin\PostController@show');

    //comments routes
    Route::get('/comments', 'Admin\CommentController@comments');
    //Route::get('/post/{id}', 'Admin\PostController@show');

    //posts routes
    Route::get('/saveds', 'Admin\SavedController@index');
    Route::get('/saved/{id}', 'Admin\SavedController@show');

    //payments routes
    Route::get('/payments', 'Admin\PaymentController@index');
    //Route::get('/saved/{id}', 'Admin\SavedController@show');


    //category routes
    Route::get('/posts/categories', 'Admin\CategoryController@post');
    Route::get('/album/categories', 'Admin\CategoryController@album');
    Route::post('/post/category/store', 'Admin\CategoryController@poststore');
    Route::post('/album/category/store', 'Admin\CategoryController@albumstore');
    Route::get('/category/{id}', 'Admin\CategoryController@show');

    //templates routes
    Route::get('/templates', 'Admin\TemplateController@index');
    Route::get('/template/create', 'Admin\TemplateController@create');
    Route::post('/template/store', 'Admin\TemplateController@store');

    //albums routes
    Route::get('/albums', 'Admin\AlbumController@index');
    Route::get('/album/create', 'Admin\AlbumController@create');
    Route::post('/album/store', 'Admin\AlbumController@store');

     //recommendations routes
     Route::get('/recommendations', 'Admin\RecommendationController@index');
     Route::get('/album/create', 'Admin\AlbumController@create');
     Route::post('/album/store', 'Admin\AlbumController@store');

     Route::get('/start-ad-check', function() {
        CheckAdPointsJob::dispatch()
            ->delay(now()->addSeconds(10));
        return response()->json(['message' => 'Ad points check job started']);
    });
});
