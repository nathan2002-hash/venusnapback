<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSupport;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = ContactSupport::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.tickets.index', [
           'tickets' => $tickets,
        ]);
    }
}
