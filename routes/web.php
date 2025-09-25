<?php

use App\Jobs\CheckAdPointsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\IncomingEntry;
use App\Models\Post;
use App\Models\PostMedia;

Route::get('/chat', function () {
    return view('emails.receipts.payment');
});

Route::get('/post/{post}/media/{media}', 'DeeplinkController@postmedia');
Route::get('/post/{post}', 'DeeplinkController@post');
Route::get('/album/{album}', 'DeeplinkController@album');
Route::get('/sponsored/{shortcode}', 'DeeplinkController@ad');
// routes/web.php
Route::get('/explore/{postId?}', 'DeeplinkController@explore')
    ->where('postId', '[0-9]+')
    ->name('explore.deeplink');

Route::get('/auto-login', function (Request $request) {
    $token = $request->query('token');

    if (!$token) {
        return redirect('/login')->with('error', 'Invalid login link');
    }

    $cacheKey = "web_login_token:$token";
    $userId = Cache::pull($cacheKey); // Retrieve and delete

    if (!$userId) {
        return redirect('/login')->with('error', 'Link expired or invalid');
    }

    $user = App\Models\User::find($userId);

    if (!$user) {
        return redirect('/login')->with('error', 'User not found');
    }

    Auth::login($user);

    return redirect('/'); // Change to your desired landing page
})->name('auto-login');

    $host = request()->header('host');
    $host = explode(':', $host)[0];
    if (in_array($host, ['app.venusnap.com', 'venusnap.com', 'www.venusnap.com'])) {
        Route::get('/', 'HomeController@home');
    } else {
        //Route::resource('home', MarketingHomeController::class);
    }

    if (in_array($host, ['payment.venusnap.com'])) {
        Route::get('/', 'PaymentController@index')->middleware('auth');
        Route::post('/create-payment-intent', 'PaymentController@createPaymentIntent')->middleware('auth');
        Route::post('/confirm-payment', 'PaymentController@confirmPayment')->middleware('auth');
    } else {
    }

    Route::get('/terms/of/service', 'HomeController@terms');
    Route::get('/welcome', 'HomeController@welcome');
    Route::get('/child/safety', 'HomeController@childsafety');
    Route::get('/terms/conditions', function () {
        return redirect('/terms/of/service');
    });
    Route::get('/privacy/policy', 'HomeController@privacy');

Route::post('/blocked', 'HomeController@blocked');
//terms routes

//Route::post('/contact', 'ContactFormController@submit')->name('contact.submit');
    $host = request()->header('host');
    $host = explode(':', $host)[0];
    if (in_array($host, ['app.venusnap.com', 'venusnap.com', 'www.venusnap.com'])) {
        //Route::get('/', 'HomeController@home');
    } else {
        //Route::resource('home', MarketingHomeController::class);
    };

Route::prefix('restricted')->middleware('auth', 'admin')->group(function () {
    Route::get('/welcome', 'Admin\WelcomeController@index')->name('dashboard');

    //user routes
    Route::get('/users', 'Admin\UserController@index');
    Route::get('/user/{id}', 'Admin\UserController@show');

     //user routes
    Route::get('/accounts', 'Admin\UserController@accounts');
    //Route::get('/user/{id}', 'Admin\UserController@show');

    //posts routes
    Route::get('/posts', 'Admin\PostController@index');
    Route::get('/post/{id}', 'Admin\PostController@show');

    //posts routes
    Route::post('/post/state/{id}', 'Admin\PostStateController@state');

    //comments routes
    Route::get('/comments', 'Admin\CommentController@comments');
    //Route::get('/post/{id}', 'Admin\PostController@show');

    //posts routes
    Route::get('/saveds', 'Admin\SavedController@index');
    Route::get('/saved/{id}', 'Admin\SavedController@show');

    //payments routes
    Route::get('/payments', 'Admin\PaymentController@index');

    //payments routes
    Route::get('/activities', 'Admin\UserAuthController@index');

    //support routes
    Route::get('/support/tickets', 'Admin\TicketController@index');
    Route::post('/support/ticket/state', 'Admin\TicketController@markstate');

    //ads routes
    Route::get('/ads', 'Admin\AdController@index');
    Route::get('/adboards', 'Admin\AdController@adboards');
    Route::post('/ads/update-status', 'Admin\AdController@updateStatus')->name('ads.updateStatus');

    //points routes
    Route::get('/points/transactions', 'Admin\PointTransactionController@index');
    Route::get('/points/manage', 'Admin\PointManageController@manage');
    Route::get('/points/allocations', 'Admin\PointManageController@allocations');
    Route::post('/points/manage/user', 'Admin\PointManageController@manageUserPoints');

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

    //communications routes
    Route::get('/communications', 'Admin\CommunicationController@index');
    Route::get('/communication/create', 'Admin\CommunicationController@create');
     Route::get('/communication/email/create', 'Admin\CommunicationController@emailcreate');
    Route::post('/communication/store', 'Admin\CommunicationController@store');

    //location routes
    Route::get('/settings/countries', 'Admin\SettingController@countries');
    Route::get('/settings/country/create', 'Admin\SettingController@countrycreate');
    Route::get('/settings/continents', 'Admin\SettingController@continents');
    Route::post('/settings/country/store', 'Admin\SettingController@storeCountry');
    Route::post('/settings/continent/store', 'Admin\SettingController@storeContinent');

    Route::get('/notices', 'Admin\AppMessageController@create')->name('admin.notices.create');
    Route::post('/notices', 'Admin\AppMessageController@store')->name('admin.notices.store');

    Route::get('/app/notices', 'Admin\AppMessageController@showPostNotificationForm');
    Route::post('/app/send/notice', 'Admin\AppMessageController@postnoticecreate')->name('admin.notices.send-post-notification');

    Route::get('/message/chats', 'Admin\MessageController@index');
    Route::get('/chat/messages', 'Admin\MessageController@getMessages')->name('admin.chat.messages');
     Route::post('/chat/send', 'Admin\MessageController@sendMessage')->name('admin.chat.send');


     Route::get('/start-ad-check', function() {
        CheckAdPointsJob::dispatch()
            ->delay(now()->addSeconds(10));
        return response()->json(['message' => 'Ad points check job started']);
    });
});
