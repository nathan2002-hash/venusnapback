<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supporter;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function support($id)
    {
        $support = new Supporter();
        $support->supporter_id = '2';
        $support->supporting_id = $id;
        $support->status = '1';
        $support->save();
        return redirect()->back();
    }
}
