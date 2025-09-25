<?php

use App\Models\Category;
use App\Models\UserSetting;
use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Crypt;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::middleware('auth:api')->group(function () {
    Route::post('/generate-web-token', function (Request $request) {
        $token = Str::random(64);
        $expiresAt = now()->addMinutes(35);

        // Store the token with user ID
        Cache::put("web_login_token:$token", $request->user()->id, $expiresAt);

        return response()->json([
            'web_url' => "https://payment.venusnap.com/auto-login?token=$token",
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);
    });
});

Route::post('/incoming/message/9s8df7as98df7asd', 'IncomingController@receive');

Route::post('/decrypt/filepath', function (Request $request) {
    try {
        $decrypted = Crypt::decryptString($request->token);
        $data = json_decode($decrypted, true);

        return response()->json([
            'file' => $data['file'],
            'expires' => $data['expires']
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Invalid token'], 401);
    }
});

Route::post('/register', 'Api\AuthController@register');
Route::post('/login', 'Api\AuthController@login');
Route::post('/forgot/password/code', 'Api\AuthController@sendResetCode');
Route::post('/reset/password', 'Api\AuthController@ResetPassword');

Route::middleware('auth:api')->post('/logout', function (Request $request) {
    $user = $request->user();
    $currentFcmToken = $request->header('Fcm-Token');

    if ($currentFcmToken) {
        FcmToken::where('user_id', $user->id)
            ->where('token', $currentFcmToken)
            ->update(['status' => 'expired']);
    }

    $user->token()->revoke();

    return response()->json([
        'message' => 'Successfully logged out'
    ]);
});

Route::middleware('auth:api')->post('/store-fcm-token', 'Api\NotificationController@storeFcmToken');

Route::middleware('auth:api')->post('/support/ticket/submit', 'Api\ContactSupportController@store');
Route::middleware('auth:api')->get('/support/tickets', 'Api\ContactSupportController@index');
Route::middleware('auth:api')->post('/support/tickets/resolve/{id}', 'Api\ContactSupportController@updateStatus');
Route::middleware('auth:api')->get('/support/faqs', 'Api\ContactSupportController@getFaqs');

Route::middleware('auth:api')->get('/addons', 'Api\TemplateController@index');

Route::middleware(['auth:api', 'check.account.status'])->group(function () {
    Route::post('/verify-2fa', 'Api\AuthController@verify2FA');
    Route::post('/resend-2fa', 'Api\AuthController@resend2FA');

    //account deletions
    Route::post('/verify-password', 'Api\AuthController@verifyPassword');
    Route::post('/account/delete', 'Api\AuthController@deleteAccount');
    Route::post('/send-otp', 'Api\AuthController@sendOTP');
    Route::post('/verify-otp', 'Api\AuthController@verifyOTP');

    //user verification
    Route::post('/verify/email/send', 'Api\VerificationController@sendEmailVerificationCode');
    Route::post('/verify/phone/send', 'Api\VerificationController@sendPhoneVerificationCode');
    Route::post('/verify/email/confirm', 'Api\VerificationController@verifyEmail');
    Route::post('/verify/phone/confirm', 'Api\VerificationController@verifyPhone');
    //profile routes
    //Route::get('/user/profile', 'Api\ProfileController@index');

    Route::get('/posts', 'Api\PostController@index');
    Route::get('/post/{post}/status', 'Api\PostController@checkStatus');


    Route::get('/explore', 'Api\PostExploreController@index');
    Route::get('/explore/albums', 'Api\ExploreController@exploreAlbums');


    Route::get('/posts/{id}', 'Api\PostController@show');

    Route::get('/user/profile', 'Api\ProfileController@index');
    Route::get('/user/profile/change', 'Api\ProfileController@changeprofile');

    Route::get('/post/comments/basic/{postMediaId}', 'Api\CommentController@getBasicComments');
    Route::get('/post/comments/replies/{commentId}', 'Api\CommentController@getCommentReplies');

    Route::post('/user/profile/update', 'Api\ProfileController@update');
    Route::post('/post/store', 'Api\PostController@store');
    Route::post('/post/store/cloud', 'Api\PostController@storecloud');

    Route::get('/post/edit/{id}', 'Api\PostController@postedit');
    Route::put('/post/update/{id}', 'Api\PostController@update');
    Route::post('/post/delete/{id}', 'Api\PostController@postdelete');

    Route::get('/post/media/state', 'Api\ViewController@more');
    Route::post('/track/post/visit', 'Api\ViewController@trackVisit');
    Route::get('/ad/sponsored/{id}', 'Api\PostExploreController@getAdById');
    Route::post('/track/explore/visit', 'Api\ViewController@trackExploreVisit');


    Route::post('/post/admire', 'Api\AdmireController@admire');
    Route::get('/post/check-like', 'Api\AdmireController@checkLike');


    //Route::post('/supporter/subscribe', 'Api\SupportController@supportpost');
    Route::post('/supporter/{action}', 'Api\SupportController@toggleSupport')
    ->where('action', 'subscribe|unsubscribe');

    Route::post('/supporter/subscribe/ad/{id}', 'Api\SupportController@supportad');
    Route::post('/supporter/album/subscribe', 'Api\SupportController@supportalbum');
    Route::get('/supporter/check-support', 'Api\SupportController@checkSupport');

    Route::get('/notifications/count', 'Api\NotificationController@notificationscount');
    Route::get('/notifications', 'Api\NotificationController@index');
    Route::post('/notifications/send-push', 'Api\NotificationController@sendPushNotification');
    Route::post('/notifications/mark-as-read', 'Api\NotificationController@markAsRead');
    Route::post('/notifications/mark-all-read', 'Api\NotificationController@markAllUserNotificationsAsRead');


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
    Route::post('/album/check/business/name', 'Api\AlbumController@businessnamecheck');
    Route::post('/album/check/creator/name', 'Api\AlbumController@creatornamecheck');
    Route::post('/album/check/general/name', 'Api\AlbumController@checkGeneralName');
    Route::post('/album/content/check', 'Api\AlbumController@checkContent');

    Route::get('/user/albums', 'Api\AlbumController@getAlbums');
    Route::get('/user/album/{id}', 'Api\AlbumController@show');
    Route::get('/user/album/viewer/{albumId}', 'Api\AlbumController@showviewer');
    Route::post('/user/album/{id}/update-image', 'Api\AlbumController@album_update');

    Route::get('/album/{album}/images', 'Api\AppStatusController@getAlbumImages');

    Route::post('/user/album/delete/{id}', 'Api\AlbumAccessController@albumdelete');

    Route::get('/album/analytics/{id}', 'Api\AlbumController@albumAnalytics');

    Route::post('/post/share/{id}', 'Api\ViewController@sharePost');

    //artwork routes
    Route::post('/artwork/save', 'Api\ArtworkController@store');
    Route::get('/user/artwork', 'Api\ArtworkController@fetchArtworks');
    Route::delete('/artwork/delete/{id}', 'Api\ArtworkController@destroy');

    //template routes
    //Route::get('/templates', 'Api\PostController@index');
    Route::post('/generate/template', 'Api\TemplateController@generateTemplate');
    Route::get('/template/status/{id}', 'Api\TemplateController@checkStatus');

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

    Route::get('/country/phone/lengths', function () {
        return response()->json([
            'US' => ['min' => 10, 'max' => 10],  // United States
            'GB' => ['min' => 10, 'max' => 10],  // United Kingdom
            'IN' => ['min' => 10, 'max' => 10],  // India
            'ZA' => ['min' => 9, 'max' => 9],    // South Africa
            'ZM' => ['min' => 12, 'max' => 12],    // Zambia
            // Add more countries as needed
        ]);
    });

    Route::get('/album/categories', 'Api\AlbumAccessController@getCategoriesByAlbum');

    Route::post('/post/view', 'Api\ViewController@view');
    Route::post('/post/marking/view/batch', 'Api\ViewController@viewpost');


    Route::post('/post/comment/{id}', 'Api\CommentController@storeComment');
    Route::post('/post/comment/reply/{id}', 'Api\CommentController@storeReply');

    Route::post('/post/delete/comment/{id}', 'Api\CommentController@commentdelete');
    Route::post('/post/delete/comment/reply/{id}', 'Api\CommentController@commentreplydelete');

    Route::post('/post/media/report', 'Api\ReportController@reportpost');
    Route::post('/report/comment/{id}', 'Api\ReportController@reportcomment');
    Route::post('/post/save', 'Api\SavedController@save');
    Route::post('/post/unsave/{postId}', 'Api\SavedController@unsave');

    Route::get('/post/saved', 'Api\SavedController@getSavedPosts');

    Route::get('/search', 'Api\SearchController@search');
    Route::post('/log-search', 'Api\SearchController@logSearch');

    Route::get('/post/history', 'Api\HistoryController@getUserHistory');
    Route::post('/post/history/delete', 'Api\HistoryController@deleteHistory');

    Route::post('/adboard/store', 'Api\AdController@adboard');
    Route::post('/ad/store', 'Api\AdController@adstore');
    Route::post('/ad/publish', 'Api\AdController@publish');
    Route::get('/user/points', 'Api\AdController@getUserPoints');
    Route::get('/user/ad/albums', 'Api\AdController@getUserAlbums');
    Route::get('/ads/{id}', 'Api\AdController@show');
    Route::post('/ad/status/{id}', 'Api\AdController@updateStatus');
    Route::post('/ad/delete/{id}', 'Api\AdController@deleteAd');
    Route::post('/ad/add-points/{ad}', 'Api\PointController@addPoints');
    Route::get('/ad/regions', 'Api\AdController@regions');
    Route::get('/ad/terms', 'Api\AdController@adTerms');
    Route::get('/ad/background/image', 'Api\AdController@fetchAdbackground');

    //ad edit
    Route::get('/adboard/edit/{id}', 'Api\AdController@adboardedit');
    Route::get('/ad/edit/{id}', 'Api\AdController@editads');
    Route::put('/adboard/update/{id}', 'Api\AdController@update');
    Route::post('/ad/update/{id}', 'Api\AdController@adupdate');

    Route::get('/ad/list', 'Api\AdController@getAds');
    Route::get('/ad/{id}/performance', 'Api\AdController@getAdPerformance');

    Route::post('/ad/seen', 'Api\PostExploreController@sendAdSeenRequest');
    Route::post('/ad/cta/click/{id}', 'Api\PostExploreController@sendAdCtaClick');
    Route::get('/ad/share/url/{adId}','Api\MoreAdController@generateShareUrl');
    Route::get('/ad/resolve/{shortCode}','Api\MoreAdController@resolveShortCode');

    // Route::post('/create/paypal/order', 'Api\PayController@payment');
    // Route::post('/capture/paypal/order', 'Api\PayController@capture');

    Route::post('/create/payment/intent', 'Api\PaymentController@createPaymentIntent');
    Route::post('/payment/confirm', 'Api\PaymentController@confirmPayment');
    Route::post('/send/receipt', 'Api\PaymentController@sendReceipt');

    Route::get('/points/config', 'Api\PaymentController@getConfig');
    Route::post('/request/points', 'Api\PaymentController@requestpoints');

    Route::get('/payments', 'Api\PaymentController@fetchUserPayments');
    Route::get('/payouts', 'Api\PayoutController@fetchUserPayouts');

    Route::get('/points', 'Api\PointController@getPoints');
    Route::get('/payment-info', 'Api\PointController@paymentinfo');

    Route::get('/user/settings/monetization/status', 'Api\SettingController@getMonetizationStatus');

    Route::get('/user/monetization/status', 'Api\MonetizationController@getMonetizationStatus');
    Route::get('/user/monetization/dashboard', 'Api\MonetizationController@getUserDashboardData');
    Route::get('/payout/details', 'Api\MonetizationController@getPayoutDetails');
    Route::post('/payout/request', 'Api\PayoutController@requestPayout');
    Route::post('/user/monetization/apply', 'Api\MonetizationController@applyForMonetization');
    Route::get('/monetization/albums/fetch', 'Api\MonetizationController@getUserAlbums');
    Route::get('/monetization/countries/fetch', 'Api\MonetizationController@countries');
    Route::get('/user/monetization/applications', 'Api\MonetizationController@getApplications');
    Route::post('/user/monetization/application/cancel/{id}', 'Api\MonetizationController@cancelApplication');
    Route::get('/user/account/payout/settings', 'Api\AccountController@getPayoutSettings');
    Route::post('/user/account/payout/update', 'Api\AccountController@savePayoutSettings');

    Route::get('/tax/information', 'Api\TaxController@getTaxInformation');
    Route::post('/tax/information/update', 'Api\TaxController@saveTaxInformation');

    //album access
    Route::get('/manage/album/{id}/access', 'Api\AlbumAccessController@accesslist');
    Route::get('/manage/album/{id}', 'Api\AlbumAccessController@albums');
    Route::put('/manage/album/update/{id}', 'Api\AlbumAccessController@albumupdate');
    Route::get('/album/requests', 'Api\AlbumAccessController@getRequests');
    Route::post('/album/requests/{id}/respond', 'Api\AlbumAccessController@respondToRequest');

    //genai
    Route::post('/generate-ad', 'Api\AIGenController@generateAd');
    Route::post('/regenerate-ad/{id}', 'Api\AIGenController@regenerateAd');
    Route::get('/generated-ads/{id}', 'Api\AIGenController@getAd');
    Route::get('/recent-ads', 'Api\AIGenController@recentAds');
    Route::get('/placeholder-ads', 'Api\AIGenController@placeholders');
    Route::get('/genai/points', 'Api\AIGenController@GenPoints');
    Route::get('/genai/images', 'Api\AIGenController@GenImages');
    Route::get('/ads/{id}/download-url', 'Api\AIGenController@getDownloadUrl');

    //artwork ai
    Route::post('/generate/ai/image', 'Api\ArtworkController@generateImage');
    Route::post('/regenerate/ai/image/{id}', 'Api\ArtworkController@regenerateImage');
    Route::get('/generated/ai/images/{id}', 'Api\ArtworkController@getImage');
    Route::get('/recent/ai/images', 'Api\ArtworkController@recentImages');
    Route::get('/prompt/ai/examples', 'Api\ArtworkController@promptExamples');
    Route::get('/artwork/ai/status/{id}', 'Api\ArtworkController@checkStatus');
    Route::get('/image/generate/points', 'Api\ArtworkController@GenPoints');
    Route::get('/artworks/{id}/download-url', 'Api\ArtworkController@getDownloadUrl');

    //media download
    Route::post('/post/media/download', 'Api\MediaDownloadController@download');
    Route::post('/post/download', 'Api\MediaDownloadController@downloadpostimages');

    //app status
    Route::get('/app/status', 'Api\AppStatusController@checkAppStatus');
    Route::post('/app/message/track', 'Api\AppStatusController@trackMessageAction');

    //user notices
    Route::get('/notices', 'Api\NoticeController@index');
    Route::get('/notices/{notice}', 'Api\NoticeController@show');
    Route::post('/notices/{notice}/mark-as-read', 'Api\NoticeController@markAsRead');
    Route::post('/notices/mark-all-read', 'Api\NoticeController@markAllAsRead');
});
