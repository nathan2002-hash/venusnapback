<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ContactSupport;
use Illuminate\Support\Facades\Auth;

class ContactSupportController extends Controller
{
    public function store(Request $request)
    {
        // Get the authenticated user's ID
        $user_id = Auth::id();

        $support = new ContactSupport();
        $support->user_id = $user_id;
        $support->category = $request->category;
        $support->priority = $request->priority;
        $support->description = $request->description;
        $support->save();
        // Return the simplified response
        return response()->json([
            'status' => 'success',
        ], 200);
    }
}
