<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function support()
    {
        $support = new Supporter();
        $support->user_id = Auth::user()->id;
        $support->artboard_id = '5';
        $support->status = '1';
        $support->save();
        return redirect()->back();
    }
}
