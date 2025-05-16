<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use Illuminate\Http\Request;

class PointTransactionController extends Controller
{
    public function index()
    {
        $pointtransactions = PointTransaction::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.points.transactions.index', [
           'pointtransactions' => $pointtransactions,
        ]);
    }
}
