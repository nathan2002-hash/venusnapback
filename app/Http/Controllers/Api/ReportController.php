<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
       //
        return view('user.reports.index', [
           //
        ]);
    }

    public function report(Request $request)
    {
        // Get the authenticated user's ID
        $user_id = Auth::id();

        $report = new Report();
        $report->user_id = $user_id;
        $report->post_media_id = $request->media_id;
        $report->status = 'active';
        $report->reason = 'default reporting';
        $report->save();
        // Return the simplified response
        return response()->json([
            'status' => 'success',
        ], 200);
    }

}
