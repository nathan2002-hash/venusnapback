<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Activity;

class UserAuthController extends Controller
{
    public function index()
    {
        $activities = Activity::with('user')
            ->orderBy('created_at', 'desc')
            ->whereIn('source', ['Authentication', 'Registration'])
            ->get();

        return view('admin.userauth.sessions', [
            'activities' => $activities,
        ]);
    }

}
