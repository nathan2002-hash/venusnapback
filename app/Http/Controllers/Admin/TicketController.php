<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ContactSupport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = ContactSupport::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.tickets.index', [
           'tickets' => $tickets,
        ]);
    }

    public function markstate(Request $request)
    {
        $contactsupport = ContactSupport::find($request->support_id);

        if ($contactsupport) {
            $contactsupport->resolved_at = Carbon::now();
            $contactsupport->status = 'Resolved';
            $contactsupport->resolved_by = Auth::id();
            $contactsupport->save();
        }

        return redirect()->back();
    }
}
