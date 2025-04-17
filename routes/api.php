<?php

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::middleware('auth:api')->post('/logout', function (Request $request) {
    $request->user()->token()->revoke();
    return response()->json([
        'message' => 'Successfully logged out'
    ]);
});


Route::post('/register', 'Api\AuthController@register');
Route::post('/login', 'Api\AuthController@login');

Route::middleware('auth:api')->post('/verify-2fa', 'Api\AuthController@verify2FA');
Route::middleware('auth:api')->post('/resend-2fa', 'Api\AuthController@resend2FA');

//account deletions
Route::middleware('auth:api')->post('/verify-password', 'Api\AuthController@verifyPassword');
Route::middleware('auth:api')->post('/account/delete', 'Api\AuthController@deleteAccount');

//profile routes
//Route::get('/user/profile', 'Api\ProfileController@index');

Route::middleware('auth:api')->get('/posts', 'Api\PostController@index');


Route::middleware('auth:api')->get('/explore', 'Api\PostExploreController@index');


Route::middleware('auth:api')->get('/posts/{id}', 'Api\PostController@show');

Route::middleware('auth:api')->get('/user/profile', 'Api\ProfileController@index');
Route::middleware('auth:api')->get('/user/profile/change', 'Api\ProfileController@changeprofile');


Route::middleware('auth:api')->get('/post/comments/{postMediaId}', 'Api\CommentController@getCommentsAndReplies');
Route::middleware('auth:api')->post('/user/profile/update', 'Api\ProfileController@update');
Route::middleware('auth:api')->post('/post/store', 'Api\PostController@store');
Route::middleware('auth:api')->post('/post/store/cloud', 'Api\PostController@storecloud');

Route::middleware('auth:api')->get('/post/media/state', 'Api\ViewController@more');
Route::middleware('auth:api')->get('/ad/sponsored/{id}', 'Api\PostExploreController@getAdById');


Route::middleware('auth:api')->post('/post/admire', 'Api\AdmireController@admire');
Route::middleware('auth:api')->get('/post/check-like', 'Api\AdmireController@checkLike');


Route::middleware('auth:api')->post('/supporter/subscribe', 'Api\SupportController@supportpost');
Route::middleware('auth:api')->post('/supporter/subscribe/ad/{id}', 'Api\SupportController@supportad');
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
Route::middleware('auth:api')->get('/user/album/viewer/{albumId}', 'Api\AlbumController@showviewer');
Route::middleware('auth:api')->post('/user/album/{id}/update-image', 'Api\AlbumController@album_update');

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

Route::middleware('auth:api')->post('/post/delete/comment/{id}', 'Api\CommentController@commentdelete');
Route::middleware('auth:api')->post('/post/delete/comment/reply/{id}', 'Api\CommentController@commentreplydelete');

Route::middleware('auth:api')->post('/post/media/report', 'Api\ReportController@report');
Route::middleware('auth:api')->post('/post/save', 'Api\SavedController@save');

Route::middleware('auth:api')->get('/search', 'Api\SearchController@search');
Route::middleware('auth:api')->post('/log-search', 'Api\SearchController@logSearch');

Route::middleware('auth:api')->get('/post/history', 'Api\HistoryController@getUserHistory');

Route::middleware('auth:api')->post('/adboard/store', 'Api\AdController@adboard');
Route::middleware('auth:api')->post('/ad/store', 'Api\AdController@adstore');
Route::middleware('auth:api')->post('/ad/publish', 'Api\AdController@publish');
Route::middleware('auth:api')->get('/user/points', 'Api\AdController@getUserPoints');
Route::middleware('auth:api')->get('/user/ad/albums', 'Api\AdController@getUserAlbums');
Route::middleware('auth:api')->get('/ads/{id}', 'Api\AdController@show');
Route::middleware('auth:api')->post('/ad/add-points/{ad}', 'Api\PointController@addPoints');

Route::middleware('auth:api')->get('/ad/list', 'Api\AdController@getAds');
Route::middleware('auth:api')->get('/ad/{id}/performance', 'Api\AdController@getAdPerformance');

Route::middleware('auth:api')->post('/ad/seen', 'Api\PostExploreController@sendAdSeenRequest');
Route::middleware('auth:api')->post('/ad/cta/click/{id}', 'Api\PostExploreController@sendAdCtaClick');

Route::middleware('auth:api')->post('/create/payment/intent', 'Api\PaymentController@payment');
Route::middleware('auth:api')->post('/payment/confirm', 'Api\PaymentController@confirmPayment');

Route::middleware('auth:api')->get('/payments', 'Api\PaymentController@fetchUserPayments');
Route::middleware('auth:api')->get('/payouts', 'Api\PayoutController@fetchUserPayouts');

Route::middleware('auth:api')->get('/points', 'Api\PointController@getPoints');

Route::middleware('auth:api')->get('/user/monetization/status', 'Api\MonetizationController@getMonetizationStatus');
Route::middleware('auth:api')->get('/user/monetization/dashboard', 'Api\MonetizationController@getUserDashboardData');
Route::middleware('auth:api')->get('/payout/details', 'Api\MonetizationController@getPayoutDetails');
Route::middleware('auth:api')->post('/payout/request', 'Api\PayoutController@requestPayout');
Route::middleware('auth:api')->post('/user/monetization/apply', 'Api\MonetizationController@applyForMonetization');

//album access
Route::middleware('auth:api')->get('/manage/album/{id}/access', 'Api\AlbumAccessController@accesslist');
Route::middleware('auth:api')->get('/manage/album/{id}', 'Api\AlbumAccessController@albums');
Route::middleware('auth:api')->put('/manage/album/update/{id}', 'Api\AlbumAccessController@albumupdate');
Route::middleware('auth:api')->get('/album/requests', 'Api\AlbumAccessController@getRequests');
Route::middleware('auth:api')->post('/album/requests/{id}/respond', 'Api\AlbumAccessController@respondToRequest');

Route::middleware('auth:api')->post('/support/ticket/submit', 'Api\ContactSupportController@store');
Route::middleware('auth:api')->get('/support/tickets', 'Api\ContactSupportController@index');
Route::middleware('auth:api')->post('/support/tickets/resolve/{id}', 'Api\ContactSupportController@updateStatus');
Route::middleware('auth:api')->get('/support/faqs', 'Api\ContactSupportController@getFaqs');
