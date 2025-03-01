<?php

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/register', 'Api\AuthController@register');
Route::post('/login', 'Api\AuthController@login');
Route::post('/google/login', 'Api\AuthController@googleLogin');

//profile routes
//Route::get('/user/profile', 'Api\ProfileController@index');

Route::get('/posts', 'Api\PostController@index');
Route::middleware('auth:api')->get('/user/profile', 'Api\ProfileController@index');
Route::middleware('auth:api')->get('/post/comments/{postMediaId}', 'Api\PostController@getCommentsAndReplies');
Route::middleware('auth:api')->post('/user/profile/update', 'Api\ProfileController@update');
Route::middleware('auth:api')->post('/post/store', 'Api\PostController@store');
Route::middleware('auth:api')->post('/post/admire', 'Api\PostController@admire');
Route::middleware('auth:api')->post('/supporter/subscribe', 'Api\SupportController@support');
Route::middleware('auth:api')->post('/change/password', 'Api\AuthController@changePassword');
Route::middleware('auth:api')->post('/password/activities', 'Api\AuthController@fetchPasswordActivities');

Route::middleware('auth:api')->get('/categories', function () {
    return response()->json(Category::all());
});

Route::middleware('auth:api')->post('/post/view', 'Api\ViewController@view');
Route::middleware('auth:api')->post('/post/comment/{id}', 'Api\PostController@storeComment');
Route::middleware('auth:api')->post('/post/comment/reply/{id}', 'Api\PostController@storeReply');

Route::middleware('auth:api')->post('/logout', function (Request $request) {
    $request->user()->token()->revoke();
    return response()->json([
        'message' => 'Successfully logged out'
    ]);
});







Route::get('/welcome', 'Api\WelcomeController@index');

//posts routes
Route::post('/post/save/{id}', 'Api\PostController@save');
//Route::post('/post/comment/{id}', 'Api\PostController@comment');
//Route::post('/post/comment/reply/{id}', 'Api\PostController@commentreply');
Route::post('/post/admire/{id}', 'Api\PostController@admire');
Route::post('/post/report/{id}', 'Api\PostController@report');

//artwork routs
Route::get('/artworks', 'Api\ArtworkController@index');
Route::post('/artwork/store', 'Api\ArtworkController@store');

//reports routes
Route::get('/reports', 'Api\ReportController@index');

//saved routes
Route::get('/saved', 'Api\SavedController@index');

//account routes
Route::get('/account', 'Api\AccountController@index');
