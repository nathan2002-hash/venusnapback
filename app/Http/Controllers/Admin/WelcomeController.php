<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ad;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use App\Models\Album;
use App\Models\Artwork;
use App\Models\Artboard;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Adboard;
use App\Models\PostMedia;

class WelcomeController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        $usersc = User::count();
        $posts = Post::count();
        $album = Album::count();
        $adboards = Adboard::count();
        $ads = Ad::count();
        $artworks = Artwork::count();
        $templates = Template::count();
        $postmedias = PostMedia::count();
        $totalPoints = User::sum('points');
        $amount = $totalPoints / 500;

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $paymentsComplete = DB::table('payments')
        ->where('status', 'completed')
        ->whereMonth('created_at', $currentMonth)
        ->whereYear('created_at', $currentYear)
        ->sum('amount');

        $paymentsPending = DB::table('payments')
            ->where('status', 'pending')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount');

        $paymentsFailed = DB::table('payments')
            ->where('status', 'failed')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount');

        $usertemplates = DB::table('templates')
        ->where('type', 'open')
        ->count();
        $venusnaptemplates = DB::table('templates')
        ->where('type', 'owned')
        ->count();
        $runningads = DB::table('ads')
        ->where('status', 'active')
        ->count();
        $pausedads = DB::table('ads')
        ->where('status', 'paused')
        ->count();
        return view('admin.welcome', [
           'users' => $users,
           'posts' => $posts,
           'postmedias' => $postmedias,
           'album' => $album,
           'usersc' => $usersc,
           'adboards' => $adboards,
           'ads' => $ads,
           'runningads' => $runningads,
           'pausedads' => $pausedads,
           'artworks' => $artworks,
           'templates' => $templates,
           'totalpoints' => $totalPoints,
           'pointamount' => $amount,
           'paymentsComplete' => $paymentsComplete,
           'paymentsPending' => $paymentsPending,
           'paymentsFailed' => $paymentsFailed,
           'usertemplates' => $usertemplates,
           'venusnaptemplates' => $venusnaptemplates,
        ]);
    }
}
