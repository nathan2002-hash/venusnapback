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
        $support->topic = $request->topic;
        $support->priority = $request->priority;
        $support->description = $request->description;
        $support->status = "Open";
        $support->save();
        // Return the simplified response
        return response()->json([
            'message' => 'Support ticket created successfully',
            'ticket_id' => $support->id,
        ], 201);
    }

    public function index(Request $request)
    {
        $tickets = ContactSupport::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $tickets->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'category' => $ticket->category,
                    'topic' => $ticket->topic,
                    'description' => $ticket->description,
                    'priority' => $ticket->priority,
                    'status' => $ticket->status,
                    'created_at' => $ticket->created_at->toDateTimeString(),
                    'updated_at' => $ticket->updated_at ? $ticket->updated_at->toDateTimeString() : null,
                ];
            })
        ]);
    }

    // In Laravel controller
    public function updateStatus(Request $request, $id)
    {
        $ticket = ContactSupport::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

        $ticket->status = 'Resolved';
        $ticket->resolved_at = now();
        $ticket->save();

        return response()->json([
            'message' => 'Ticket status updated',
            'ticket' => $ticket
        ]);
    }

public function getFaqs()
{
    $faqs = [
        [
            'question' => 'How do I request a payout?',
            'answer' => 'Go to Monetization Settings → Payment Method and follow the instructions to set up your payout account.',
            'category' => 'payments'
        ],
        [
            'question' => 'Why is my account not monetized yet?',
            'answer' => 'Monetization requires approval. Ensure you meet all platform guidelines and have completed verification.',
            'category' => 'monetization'
        ],
        [
            'question' => 'How are taxes handled?',
            'answer' => 'For earnings over $600/year, we provide tax forms. Update your tax info in Settings.',
            'category' => 'taxes'
        ],
        // Add more FAQs as needed
    ];

    return response()->json([
        'data' => $faqs
    ]);
}
}
