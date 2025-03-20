<?php

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/register', 'Api\AuthController@register');
Route::post('/login', 'Api\AuthController@login');

Route::middleware('auth:api')->post('/verify-2fa', 'Api\AuthController@verify2FA');
Route::middleware('auth:api')->post('/resend-2fa', 'Api\AuthController@resend2FA');

//profile routes
//Route::get('/user/profile', 'Api\ProfileController@index');

Route::middleware('auth:api')->get('/posts', 'Api\PostController@index');
Route::get('/posts/{id}', 'Api\PostController@show');

Route::middleware('auth:api')->get('/user/profile', 'Api\ProfileController@index');
Route::middleware('auth:api')->get('/user/profile/change', 'Api\ProfileController@changeprofile');


Route::middleware('auth:api')->get('/post/comments/{postMediaId}', 'Api\CommentController@getCommentsAndReplies');
Route::middleware('auth:api')->post('/user/profile/update', 'Api\ProfileController@update');
Route::middleware('auth:api')->post('/post/store', 'Api\PostController@store');
Route::middleware('auth:api')->post('/post/store/cloud', 'Api\PostController@storecloud');

Route::middleware('auth:api')->get('/post/media/state', 'Api\ViewController@more');


Route::middleware('auth:api')->post('/post/admire', 'Api\AdmireController@admire');
Route::middleware('auth:api')->get('/post/check-like', 'Api\AdmireController@checkLike');


Route::middleware('auth:api')->post('/supporter/subscribe', 'Api\SupportController@support');
Route::middleware('auth:api')->get('/supporter/check-support', 'Api\SupportController@checkSupport');

Route::middleware('auth:api')->get('/notifications/count', 'Api\NotificationController@notificationscount');
Route::middleware('auth:api')->get('/notifications', 'Api\NotificationController@index');
Route::middleware('auth:api')->post('/notifications/mark-as-read', 'Api\NotificationController@markAsRead');


Route::middleware('auth:api')->post('/change/password', 'Api\AuthController@changePassword');
Route::middleware('auth:api')->get('/password/activities', 'Api\AuthController@fetchPasswordActivities');

//user posts route
Route::middleware('auth:api')->get('/user/recent/posts', 'Api\PostController@getRecentPosts');
Route::middleware('auth:api')->get('/user/all/posts', 'Api\PostController@getPosts');

//album routes
Route::middleware('auth:api')->get('/albums', 'Api\AlbumController@getUserAlbums');
Route::middleware('auth:api')->post('/album/store', 'Api\AlbumController@store');
Route::middleware('auth:api')->post('/album/personal/store', 'Api\AlbumController@personalstore');
Route::middleware('auth:api')->post('/album/creator/store', 'Api\AlbumController@creatorstore');
Route::middleware('auth:api')->post('/album/business/store', 'Api\AlbumController@businessstore');
Route::middleware('auth:api')->get('/album/content/types/creator', 'Api\AlbumController@albumcategorycreator');
Route::middleware('auth:api')->get('/album/business/categories', 'Api\AlbumController@albumcategorybusiness');

Route::middleware('auth:api')->get('/user/albums', 'Api\AlbumController@getAlbums');
Route::middleware('auth:api')->get('/user/album/{id}', 'Api\AlbumController@show');

//artwork routes
Route::middleware('auth:api')->post('/artwork/save', 'Api\ArtworkController@store');
Route::middleware('auth:api')->get('/user/artwork', 'Api\ArtworkController@fetchArtworks');

//template routes
Route::middleware('auth:api')->get('/templates', 'Api\TemplateController@index');

//preference routes
Route::middleware('auth:api')->get('/preference/categories', 'Api\PreferenceController@index');
Route::middleware('auth:api')->get('/content/preference/categories', 'Api\PreferenceController@contentpre');
Route::middleware('auth:api')->post('/user/preferences', 'Api\PreferenceController@storeUserPreferences');
Route::middleware('auth:api')->post('/user/content/preferences', 'Api\PreferenceController@storeUserContentPreferences');

//settings route
Route::middleware('auth:api')->get('/user/settings', 'Api\SettingController@getUserSettings');
Route::middleware('auth:api')->patch('/user/update/setting', 'Api\SettingController@updateUserSetting');
Route::middleware('auth:api')->get('/user/login/activities', 'Api\AuthController@getLoginActivities');

Route::middleware('auth:api')->get('/categories', function () {
    return response()->json(Category::all());
});

Route::middleware('auth:api')->post('/post/view', 'Api\ViewController@view');
Route::middleware('auth:api')->post('/post/marking/view/batch', 'Api\ViewController@viewpost');


Route::middleware('auth:api')->post('/post/comment/{id}', 'Api\CommentController@storeComment');
Route::middleware('auth:api')->post('/post/comment/reply/{id}', 'Api\CommentController@storeReply');

Route::middleware('auth:api')->post('/post/media/report', 'Api\ReportController@report');
Route::middleware('auth:api')->post('/post/save', 'Api\SavedController@save');

Route::middleware('auth:api')->get('/search', 'Api\SearchController@search');

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
