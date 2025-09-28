<?php

namespace App\Http\Controllers;

use App\Models\ButtonClick;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ButtonClickController extends Controller
{
   public function store(Request $request)
    {
        ButtonClick::create([
            'button_name' => $request->button_name,
            'page_url'    => $request->page_url,
            'ip_address'  => $request->header('do-connecting-ip'),
            'user_agent'  => $request->header('user-agent'), // capture browser/device
            'user_id'     => Auth::id(), // null if guest
        ]);

        return response()->json(['status' => 'ok']);
    }

}
