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

Route::middleware('auth:api')->post('/logout', function (Request $request) {
    $request->user()->token()->revoke();
    return response()->json([
        'message' => 'Successfully logged out'
    ]);
});

Route::middleware('auth:api')->post('/support/ticket/submit', 'Api\ContactSupportController@store');
Route::middleware('auth:api')->get('/support/tickets', 'Api\ContactSupportController@index');
Route::middleware('auth:api')->post('/support/tickets/resolve/{id}', 'Api\ContactSupportController@updateStatus');
Route::middleware('auth:api')->get('/support/faqs', 'Api\ContactSupportController@getFaqs');


Route::middleware(['auth:api', 'check.account.status'])->group(function () {
    Route::post('/verify-2fa', 'Api\AuthController@verify2FA');
    Route::post('/resend-2fa', 'Api\AuthController@resend2FA');

    //account deletions
    Route::post('/verify-password', 'Api\AuthController@verifyPassword');
    Route::post('/account/delete', 'Api\AuthController@deleteAccount');
    Route::post('/send-otp', 'Api\AuthController@sendOTP');
    Route::post('/verify-otp', 'Api\AuthController@verifyOTP');

    //profile routes
    //Route::get('/user/profile', 'Api\ProfileController@index');

    Route::get('/posts', 'Api\PostController@index');


    Route::get('/explore', 'Api\PostExploreController@index');


    Route::get('/posts/{id}', 'Api\PostController@show');

    Route::get('/user/profile', 'Api\ProfileController@index');
    Route::get('/user/profile/change', 'Api\ProfileController@changeprofile');


    Route::get('/post/comments/{postMediaId}', 'Api\CommentController@getCommentsAndReplies');
    Route::post('/user/profile/update', 'Api\ProfileController@update');
    Route::post('/post/store', 'Api\PostController@store');
    Route::post('/post/store/cloud', 'Api\PostController@storecloud');

    Route::get('/post/media/state', 'Api\ViewController@more');
    Route::get('/ad/sponsored/{id}', 'Api\PostExploreController@getAdById');


    Route::post('/post/admire', 'Api\AdmireController@admire');
    Route::get('/post/check-like', 'Api\AdmireController@checkLike');


    Route::post('/supporter/subscribe', 'Api\SupportController@supportpost');
    Route::post('/supporter/subscribe/ad/{id}', 'Api\SupportController@supportad');
    Route::get('/supporter/check-support', 'Api\SupportController@checkSupport');

    Route::get('/notifications/count', 'Api\NotificationController@notificationscount');
    Route::get('/notifications', 'Api\NotificationController@index');
    Route::post('/notifications/mark-as-read', 'Api\NotificationController@markAsRead');


    Route::post('/change/password', 'Api\AuthController@changePassword');
    Route::get('/password/activities', 'Api\AuthController@fetchPasswordActivities');

    //user posts route
    Route::get('/user/recent/posts', 'Api\PostController@getRecentPosts');
    Route::get('/user/all/posts', 'Api\PostController@getPosts');

    //album routes
    Route::get('/albums', 'Api\AlbumController@getUserAlbums');
    Route::post('/album/store', 'Api\AlbumController@store');
    Route::post('/album/personal/store', 'Api\AlbumController@personalstore');
    Route::post('/album/creator/store', 'Api\AlbumController@creatorstore');
    Route::post('/album/business/store', 'Api\AlbumController@businessstore');
    Route::get('/album/content/types/creator', 'Api\AlbumController@albumcategorycreator');
    Route::get('/album/business/categories', 'Api\AlbumController@albumcategorybusiness');

    Route::get('/user/albums', 'Api\AlbumController@getAlbums');
    Route::get('/user/album/{id}', 'Api\AlbumController@show');
    Route::get('/user/album/viewer/{albumId}', 'Api\AlbumController@showviewer');
    Route::post('/user/album/{id}/update-image', 'Api\AlbumController@album_update');

    //artwork routes
    Route::post('/artwork/save', 'Api\ArtworkController@store');
    Route::get('/user/artwork', 'Api\ArtworkController@fetchArtworks');

    //template routes
    Route::get('/templates', 'Api\TemplateController@index');

    //preference routes
    Route::get('/preference/categories', 'Api\PreferenceController@index');
    Route::get('/content/preference/categories', 'Api\PreferenceController@contentpre');
    Route::post('/user/preferences', 'Api\PreferenceController@storeUserPreferences');
    Route::post('/user/content/preferences', 'Api\PreferenceController@storeUserContentPreferences');

    //settings route
    Route::get('/user/settings', 'Api\SettingController@getUserSettings');
    Route::patch('/user/update/setting', 'Api\SettingController@updateUserSetting');
    Route::get('/user/login/activities', 'Api\AuthController@getLoginActivities');

    Route::get('/categories', function () {
        return response()->json(Category::all());
    });

    Route::post('/post/view', 'Api\ViewController@view');
    Route::post('/post/marking/view/batch', 'Api\ViewController@viewpost');


    Route::post('/post/comment/{id}', 'Api\CommentController@storeComment');
    Route::post('/post/comment/reply/{id}', 'Api\CommentController@storeReply');

    Route::post('/post/delete/comment/{id}', 'Api\CommentController@commentdelete');
    Route::post('/post/delete/comment/reply/{id}', 'Api\CommentController@commentreplydelete');

    Route::post('/post/media/report', 'Api\ReportController@report');
    Route::post('/post/save', 'Api\SavedController@save');

    Route::get('/search', 'Api\SearchController@search');
    Route::post('/log-search', 'Api\SearchController@logSearch');

    Route::get('/post/history', 'Api\HistoryController@getUserHistory');

    Route::post('/adboard/store', 'Api\AdController@adboard');
    Route::post('/ad/store', 'Api\AdController@adstore');
    Route::post('/ad/publish', 'Api\AdController@publish');
    Route::get('/user/points', 'Api\AdController@getUserPoints');
    Route::get('/user/ad/albums', 'Api\AdController@getUserAlbums');
    Route::get('/ads/{id}', 'Api\AdController@show');
    Route::post('/ad/status/{id}', 'Api\AdController@updateStatus');
    Route::post('/ad/delete/{id}', 'Api\AdController@deleteAd');
    Route::post('/ad/add-points/{ad}', 'Api\PointController@addPoints');

    //ad edit
    Route::get('/adboard/edit/{id}', 'Api\AdController@adboardedit');
    Route::get('/ad/edit/{id}', 'Api\AdController@editads');
    Route::put('/adboard/update/{id}', 'Api\AdController@update');

    Route::get('/ad/list', 'Api\AdController@getAds');
    Route::get('/ad/{id}/performance', 'Api\AdController@getAdPerformance');

    Route::post('/ad/seen', 'Api\PostExploreController@sendAdSeenRequest');
    Route::post('/ad/cta/click/{id}', 'Api\PostExploreController@sendAdCtaClick');

    Route::post('/create/payment/intent', 'Api\PaymentController@payment');
    Route::post('/payment/confirm', 'Api\PaymentController@confirmPayment');

    Route::get('/payments', 'Api\PaymentController@fetchUserPayments');
    Route::get('/payouts', 'Api\PayoutController@fetchUserPayouts');

    Route::get('/points', 'Api\PointController@getPoints');

    Route::get('/user/monetization/status', 'Api\MonetizationController@getMonetizationStatus');
    Route::get('/user/monetization/dashboard', 'Api\MonetizationController@getUserDashboardData');
    Route::get('/payout/details', 'Api\MonetizationController@getPayoutDetails');
    Route::post('/payout/request', 'Api\PayoutController@requestPayout');
    Route::post('/user/monetization/apply', 'Api\MonetizationController@applyForMonetization');

    //album access
    Route::get('/manage/album/{id}/access', 'Api\AlbumAccessController@accesslist');
    Route::get('/manage/album/{id}', 'Api\AlbumAccessController@albums');
    Route::put('/manage/album/update/{id}', 'Api\AlbumAccessController@albumupdate');
    Route::get('/album/requests', 'Api\AlbumAccessController@getRequests');
    Route::post('/album/requests/{id}/respond', 'Api\AlbumAccessController@respondToRequest');

});
